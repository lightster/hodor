<?php

namespace Hodor;

use Exception;
use PHPUnit_Framework_TestCase;

class FlowTest extends PHPUnit_Framework_TestCase
{
    private $config_file;
    private $e_config_file;
    private $db_name;
    private $e_db_host;

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
    }

    public function tearDown()
    {
        parent::tearDown();

        if (file_exists($this->config_file)) {
            unlink($this->config_file);
        }
        $this->runCommand("psql -c 'drop database if exists {$this->db_name};' -h {$this->e_db_host} -U postgres");
    }

    public function testJobCanBeRan()
    {
        $bin_path = __DIR__ . '/../../../bin';

        $job_name = 'job-name-' . uniqid();
        $job_params = ['the_time' => date('c'), 'known_value' => 'donuts'];
        $e_job_name = escapeshellarg($job_name);
        $e_job_params = escapeshellarg(json_encode($job_params));

        $this->runCommand(
            "php {$bin_path}/test-publisher.php"
            . " -c {$this->e_config_file}"
            . " -q the-worker-q-name"
            . " --job-name {$e_job_name}"
            . " --job-params {$e_job_params}"
        );
        $this->runCommand("php {$bin_path}/buffer-worker.php -c {$this->e_config_file} -q default");
        $this->runCommand("php {$bin_path}/superqueuer.php -c {$this->e_config_file}");

        $this->assertEquals(
            json_encode([
                'name'   => $job_name,
                'params' => $job_params,
            ]),
            $this->runCommand("php {$bin_path}/job-worker.php -c {$this->e_config_file} -q the-worker-q-name")
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
                . "  > Output:    {$output}\n\n"
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
    echo json_encode([
        'name'   => $name,
        'params' => $params,
    ]);
}
PHP;
    }
}
