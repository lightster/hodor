<?php

namespace Hodor;

use Exception;
use Hodor\Database\Adapter\Postgres\Factory;
use PHPUnit_Framework_TestCase;

class FlowTest extends PHPUnit_Framework_TestCase
{
    private $config_file;
    private $e_config_file;
    private $db_name;
    private $e_db_host;

    /**
     * @var Factory
     */
    private $db_factory;

    public function setUp()
    {
        parent::setUp();

        $config = $this->generateConfigArray();
        $this->config_file = $this->writeConfigFile($config);
        $this->e_config_file = escapeshellarg($this->config_file);

        $phpmig_bin = __DIR__ . '/../../../vendor/bin/phpmig';

        $this->db_name = $config['superqueue']['database']['dbname'];
        $this->e_db_host = $config['superqueue']['database']['host'];

        $this->runCommand("psql -c 'create database {$this->db_name};' -h {$this->e_db_host} -U postgres");
        $this->runCommand("HODOR_CONFIG={$this->e_config_file} {$phpmig_bin} migrate");

        $this->db_factory = new Factory($config['superqueue']['database']);
    }

    public function tearDown()
    {
        parent::tearDown();

        if (file_exists($this->config_file)) {
            unlink($this->config_file);
        }

        unset($this->db_factory);
        // without forcing garbage collection, the DB connections
        // are not guaranteed to be disconnected; force GC
        gc_collect_cycles();

        $this->runCommand("psql -c 'drop database if exists {$this->db_name};' -h {$this->e_db_host} -U postgres");
    }

    public function testJobCanBeRan()
    {
        $job_name = 'job-name-' . uniqid();
        $job_params = [
            'the_time'    => date('c'),
            'known_value' => 'donuts',
            'job_options' => [
                'job_rank' => 5,
            ],
        ];

        $this->queueJobs($job_name, [$job_params]);

        $this->assertJobRan($job_name, $job_params);
    }

    public function testJobCanBeScheduled()
    {
        $job_name = 'job-name-' . uniqid();
        $job_params = [
            'the_time'    => date('c'),
            'known_value' => 'donuts',
            'job_options' => [
                'job_rank' => 5,
                'mutex_id' => 'chocolate',
            ],
        ];
        $scheduled_job_params = [
            'the_time'    => date('c'),
            'known_value' => 'donuts',
            'job_options' => [
                'run_after' => date('c', time() + 3),
                'job_rank' => 1,
                'mutex_id' => 'chocolate',
            ],
        ];

        $this->queueJobs($job_name, [$job_params, $job_params, $scheduled_job_params]);

        $this->assertJobRan($job_name, $job_params);
        sleep(5);
        $this->runSuperqueuer();
        $this->assertJobRan($job_name, $scheduled_job_params);
        $this->runSuperqueuer();
        $this->assertJobRan($job_name, $job_params);
    }

    public function testMutexJobsAreProperlyMutexed()
    {
        $job_name = 'job-name-' . uniqid();
        $jobs = [
            1 => ['job_number' => 1, 'job_options' => ['mutex_id' => 'mutex-a', 'job_rank' => 5]],
            2 => ['job_number' => 2, 'job_options' => ['mutex_id' => 'mutex-a', 'job_rank' => 5]],
            3 => ['job_number' => 3, 'job_options' => ['mutex_id' => 'mutex-b', 'job_rank' => 6]],
        ];

        $this->queueJobs($job_name, $jobs);

        foreach ([1, 3] as $job_idx) {
            $this->assertJobRan($job_name, $jobs[$job_idx]);
        }
    }

    public function testJobMissingFromDbCanStillBeAcknowledged()
    {
        $job_name = 'job-name-' . uniqid();
        $job_params = [
            'the_time'    => date('c'),
            'known_value' => 'donuts',
            'job_options' => [
                'job_rank' => 5,
            ],
        ];

        $this->queueJobs($job_name, [$job_params]);
        $this->runCommand("psql -c 'DELETE FROM queued_jobs;' -h {$this->e_db_host} -U postgres '{$this->db_name}'");
        $this->queueJobs($job_name, [$job_params]);

        // the first job should throw an exception because it is missing
        // from the database, but the job should still be acknowledged
        // and the second job should run as normal
        try {
            $this->runJobWorker();
        } catch (Exception $exception) {
            $this->assertJobRan($job_name, $job_params);
        }
    }

    public function testNonFatalErrorInJobResultsInZeroExitCode()
    {
        $job_name = 'non_fatal_error';
        $job_params = [];

        $this->queueJobs($job_name, [$job_params]);
        try {
            $this->runJobWorker();
        } catch (Exception $exception) {
            $this->assertEquals(0, $exception->getCode());
        }
    }

    public function testFatalErrorInJobResultsInNonZeroExitCode()
    {
        $job_name = 'fatal_error';
        $job_params = [];

        $this->queueJobs($job_name, [$job_params]);
        try {
            $this->runJobWorker();
        } catch (Exception $exception) {
            $this->assertGreaterThan(0, $exception->getCode());
        }
    }

    public function testExceptionInJobResultsInNonZeroExitCode()
    {
        $job_name = 'exception';
        $job_params = [];

        $this->queueJobs($job_name, [$job_params]);
        try {
            $this->runJobWorker();
        } catch (Exception $exception) {
            $this->assertGreaterThan(0, $exception->getCode());
        }
    }

    public function testOnlyOneSuperqueuerCanRunAtOnce()
    {
        $this->db_factory->getSuperqueuer()->requestAdvisoryLock('superqueuer', 'default');

        $job_name = 'job-name-' . uniqid();
        $job_params = [
            'the_time'    => date('c'),
            'known_value' => 'donuts',
            'job_options' => [
                'job_rank' => 5,
            ],
        ];
        $this->queueJobs($job_name, [$job_params]);

        $count = 0;
        foreach ($this->db_factory->getSuperqueuer()->getJobsToRunGenerator() as $job) {
            ++$count;
        }

        $this->assertEquals(1, $count);
    }

    /**
     * @param string $bin
     * @return string
     */
    private function getBinPath($bin)
    {
        return escapeshellarg(__DIR__ . '/../../../bin/' . $bin);
    }

    /**
     * @param string $job_name
     * @param array $job_params
     */
    private function assertJobRan($job_name, array $job_params)
    {
        $this->assertEquals(
            json_encode(
                [
                    'name' => $job_name,
                    'params' => $job_params,
                ]
            ),
            $this->runJobWorker()
        );
    }

    /**
     * @param string $job_name
     * @param array $jobs
     */
    private function queueJobs($job_name, array $jobs)
    {
        foreach ($jobs as $job) {
            $this->publishJob($job_name, $job);
            $this->runBufferWorker();
        }
        $this->runSuperqueuer();
    }

    /**
     * @param $job_name
     * @param array $job_params
     * @throws Exception
     */
    private function publishJob($job_name, array $job_params)
    {
        $e_job_name = escapeshellarg($job_name);
        $e_job_params = escapeshellarg(json_encode($job_params));

        $this->runCommand(
            "php {$this->getBinPath('test-publisher.php')}"
            . " -c {$this->e_config_file}"
            . " -q the-worker-q-name"
            . " --job-name {$e_job_name}"
            . " --job-params {$e_job_params}"
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    private function runBufferWorker()
    {
        return $this->runCommand(
            "php {$this->getBinPath('buffer-worker.php')} -c {$this->e_config_file} -q default"
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    private function runSuperqueuer()
    {
        return $this->runCommand(
            "php {$this->getBinPath('superqueuer.php')} -c {$this->e_config_file}"
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    private function runJobWorker()
    {
        return $this->runCommand(
            "php {$this->getBinPath('job-worker.php')} -c {$this->e_config_file} -q the-worker-q-name"
        );
    }

    /**
     * @param string $command
     * @throws Exception
     */
    private function runCommand($command)
    {
        $output_lines = null;
        $return_var = null;
        exec("{$command} 2>&1", $output_lines, $exit_code);

        $output = implode("\n", $output_lines);

        if ($exit_code) {
            throw new Exception(
                "An error occurred runninga command:\n"
                    . "  > Command:   {$command}\n"
                    . "  > Exit code: {$exit_code}\n"
                    . "  > Output:    {$output}\n\n",
                $exit_code
            );
        }

        return $output;
    }

    /**
     * @param array $config
     * @throws Exception
     */
    private function writeConfigFile(array $config)
    {
        $job_runner = $this->getJobRunnerCallableString();
        $config_array_string = var_export($config, true);

        $config_string = <<<PHP
<?php
return $config_array_string;
PHP;
        $config_string = str_replace("'__JOB_RUNNER__'", $job_runner, $config_string);

        $config_dir = $file_name = __DIR__ . '/../../tmp';
        $file_name = $config_dir . '/config.' . uniqid() . '.php';
        if (!is_dir($config_dir) && !mkdir($config_dir)) {
            throw new Exception("Could not create directory '{$config_dir}'.");
        }
        if (!file_put_contents($file_name, $config_string)) {
            throw new Exception("Could not write file '{$file_name}'.");
        }

        return $file_name;
    }

    private function generateConfigArray()
    {
        $template_config_path = __DIR__ . '/../../../config/dist/config.dist.php';

        $config_path = __DIR__ . '/../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config_template = require $template_config_path;
        $config_credentials = require $config_path;

        $queue_prefix = $config_credentials['test']['rabbitmq']['queue_prefix'];
        $buffer_queue_prefix = "{$queue_prefix}-buffer-" . uniqid() . "-";
        $worker_queue_prefix = "{$queue_prefix}-worker-" . uniqid() . "-";

        $db_credential_template = $config_credentials['test']['db']['yo-pdo-pgsql'];
        $db_credentials = $this->parseDsn($db_credential_template['dsn']);
        $db_credentials['dbname'] .= '_' . uniqid();
        $dsn = $this->generateDsn($db_credentials);

        $config = $config_template;
        $config['superqueue']['database'] = [
            'type'     => 'pgsql',
            'dsn'      => $dsn,
            'username' => $config_credentials['test']['db']['yo-pdo-pgsql']['username'],
            'password' => $config_credentials['test']['db']['yo-pdo-pgsql']['password'],
            'host'     => $db_credentials['host'],
            'dbname'   => $db_credentials['dbname'],
        ];
        $config['buffer_queue_defaults']['queue_prefix'] = $buffer_queue_prefix;
        $config['worker_queue_defaults']['queue_prefix'] = $worker_queue_prefix;
        $config['worker_queues']['the-worker-q-name'] = [
            'workers_per_server' => 1,
        ];
        $config['job_runner'] = '__JOB_RUNNER__';

        return $config;
    }

    private function parseDsn($dsn)
    {
        list($type, $arg_string) = explode(':', $dsn);
        $arg_pairs = explode(';', $arg_string);

        $args = [
            '_type' => $type,
        ];
        foreach ($arg_pairs as $pair) {
            list($key, $val) = explode('=', $pair);
            $args[$key] = $val;
        }

        return $args;
    }

    private function generateDsn(array $args)
    {
        $type = $args['_type'];
        unset($args['_type']);

        $arg_pairs = [];
        foreach ($args as $key => $val) {
            $arg_pairs[] = "{$key}={$val}";
        }
        $arg_string = implode(";", $arg_pairs);

        return "{$type}:{$arg_string}";
    }

    private function getJobRunnerCallableString()
    {
        return <<<'PHP'
function($name, $params) {
    if ('non_fatal_error' === $name) {
        echo $z;
        return;
    }
    if ('fatal_error' === $name) {
        call_undefined_function();
        return;
    }
    if ('exception' === $name) {
        throw new Exception('exception');
        return;
    }

    echo json_encode([
        'name'   => $name,
        'params' => $params,
    ]);
}
PHP;
    }
}
