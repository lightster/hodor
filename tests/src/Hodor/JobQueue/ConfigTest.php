<?php

namespace Hodor\JobQueue;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfigPath
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testConfigPathIsSet($options)
    {
        $path = __FILE__ . '.' . uniqid();

        $config = new Config($path, $options);

        $this->assertEquals(
            $path,
            $config->getConfigPath()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getDatabaseConfig
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testDatabaseConfigCanBeRetrieved($options)
    {
        $config = new Config(__FILE__, $options);

        $db_config = $config->getDatabaseConfig();

        $this->assertEquals(
            [
                'type' => $options['superqueue']['database']['type'],
                'dsn'  => $options['superqueue']['database']['dsn'],
            ],
            [
                'type' => $db_config['type'],
                'dsn'  => $db_config['dsn'],
            ]
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getDatabaseConfig
     * @covers ::<private>
     * @expectedException \Exception
     */
    public function testRetrievingDatabaseConfigThrowsExceptionIfSuperqueuerConfigIsNotDefined()
    {
        $config = new Config(__FILE__, []);

        $config->getDatabaseConfig();
    }

    /**
     * @covers ::__construct
     * @covers ::getDatabaseConfig
     * @covers ::<private>
     * @expectedException \Exception
     */
    public function testRetrievingDatabaseConfigThrowsExceptionIfDbConfigIsNotDefined()
    {
        $config = new Config(__FILE__, ['superqueuer' => []]);

        $config->getDatabaseConfig();
    }

    /**
     * @covers ::__construct
     * @covers ::getJobQueueConfig
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testJobQueueConfigIsReused($options)
    {
        $config = new Config(__FILE__, $options);

        $this->assertSame($config->getJobQueueConfig(), $config->getJobQueueConfig());
    }

    /**
     * @covers ::__construct
     * @covers ::getJobQueueConfig
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testJobQueueConfigOptionsArePassedIn($options)
    {
        $config = new Config(__FILE__, $options);

        $job_queue_config = $config->getJobQueueConfig();

        $uniqid = uniqid();
        $this->assertSame(
            $uniqid,
            $job_queue_config->getBufferQueueName($uniqid, [], [])
        );
        $this->assertSame(
            $uniqid,
            $job_queue_config->getWorkerQueueName($uniqid, [], [])
        );
        $this->assertSame(
            [$uniqid, ['value' => $uniqid]],
            call_user_func($job_queue_config->getJobRunnerFactory(), $uniqid, ['value' => $uniqid], [])
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getMessageQueueConfig
     * @covers ::<private>
     * @dataProvider configProvider
     * @param array $options
     */
    public function testMessageQueueConfigIsReused(array $options)
    {
        $config = new Config(__FILE__, $options);

        $this->assertSame($config->getMessageQueueConfig(), $config->getMessageQueueConfig());
    }

    /**
     * @covers ::__construct
     * @covers ::getMessageQueueConfig
     * @covers ::<private>
     * @dataProvider configProvider
     * @param array $options
     * @throws Exception
     */
    public function testMessageQueueConfigOptionsArePassedIn(array $options)
    {
        $config = new Config(__FILE__, $options);

        $buffer_config = $config->getMessageQueueConfig()->getQueueConfig('bufferer-default');
        $worker_config = $config->getMessageQueueConfig()->getQueueConfig('worker-default');

        $this->assertEquals(
            [
                $options['queue_defaults']['host'],
                $options['buffer_queue_defaults']['username'],
                "{$options['buffer_queue_defaults']['queue_prefix']}default",
                $options['queue_defaults']['host'],
                $options['worker_queue_defaults']['username'],
                "{$options['worker_queue_defaults']['queue_prefix']}default",
            ],
            [
                $buffer_config['host'],
                $buffer_config['username'],
                $buffer_config['queue_name'],
                $worker_config['host'],
                $worker_config['username'],
                $worker_config['queue_name'],
            ]
        );

        $this->assertEquals(
            ['type' => 'testing'],
            $config->getMessageQueueConfig()->getAdapterFactoryConfig()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerConfig
     * @covers ::<private>
     * @dataProvider configProvider
     * @param array $options
     */
    public function testWorkerConfigIsReused(array $options)
    {
        $config = new Config(__FILE__, $options);

        $this->assertSame($config->getWorkerConfig(), $config->getWorkerConfig());
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerConfig
     * @covers ::<private>
     * @dataProvider configProvider
     * @param array $options
     * @throws Exception
     */
    public function testWorkerConfigOptionsArePassedIn(array $options)
    {
        $config = new Config(__FILE__, $options);
        $codebase_path = dirname(dirname(dirname(dirname(__DIR__))));
        $base_path = "{$codebase_path}/src/Hodor/JobQueue/Config/../../../../bin";

        $this->assertEquals(
            [
                'superqueuer-default' => [
                    'worker_type'   => 'superqueuer',
                    'worker_name'   => 'default',
                    'process_count' => 1,
                    'command'       => "{$base_path}/superqueuer.php",
                ],
                'bufferer-default' => [
                    'worker_type'   => 'bufferer',
                    'worker_name'   => 'default',
                    'process_count' => 5,
                    'command'       => "{$base_path}/buffer-worker.php",
                ],
                'worker-default' => [
                    'worker_type'   => 'worker',
                    'worker_name'   => 'default',
                    'process_count' => 5,
                    'command'       => "{$base_path}/job-worker.php",
                ],
            ],
            $config->getWorkerConfig()->getWorkerConfigs()
        );
    }

    /**
     * @covers ::<private>
     * @dataProvider configProvider
     * @param array $options
     */
    public function testQueueConfigCanBeRequestedMultipleTimes(array $options)
    {
        $config = new Config(__FILE__, $options);

        $this->assertInstanceOf(
            'Hodor\JobQueue\Config\MessageQueueConfig',
            $config->getMessageQueueConfig()
        );
        $this->assertInstanceOf(
            'Hodor\JobQueue\Config\WorkerConfig',
            $config->getWorkerConfig()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getDaemonConfig
     * @covers ::getOption
     * @dataProvider configProvider
     */
    public function testDaemonConfigCanBeRetrieved($options)
    {
        $config = new Config(__FILE__, $options);

        $this->assertEquals(
            $options['daemon'],
            $config->getDaemonConfig()
        );
    }

    public function configProvider()
    {
        return [
            [[
                'superqueue' => [
                    'database' => [
                        'type' => 'pgsql',
                        'dsn'  => 'host=localhost user=test_hodor dbname=test_hodor',
                    ],
                ],
                'message_queue_factory' => [
                    'type' => 'testing'
                ],
                'queue_defaults' => [
                    'host' => 'queue-default-host',
                ],
                'buffer_queue_defaults' => [
                    'username'     => 'buffer-queue-default-username',
                    'queue_prefix' => 'buffer-queue-default-prefix',
                ],
                'buffer_queues' => [
                    'default' => [
                        'bufferers_per_server' => 5,
                    ],
                ],
                'worker_queue_defaults' => [
                    'username'     => 'worker-queue-default-username',
                    'queue_prefix' => 'worker-queue-default-prefix',
                ],
                'worker_queues' => [
                    'default' => [
                        'workers_per_server' => 5,
                    ],
                ],
                'worker_queue_name_factory' => function($name, $params, $options) {
                    return $name;
                },
                'buffer_queue_name_factory' => function($name, $params, $options) {
                    return $name;
                },
                'job_runner' => function($name, $params) {
                    return [$name, $params];
                },
                'daemon' => [
                    'type' => 'supervisord',
                ]
            ]],
        ];
    }
}
