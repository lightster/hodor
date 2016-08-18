<?php

namespace Hodor\JobQueue;

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfigPath
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
     * @expectedException \Exception
     */
    public function testRetrievingDatabaseConfigThrowsExceptionIfDbConfigIsNotDefined()
    {
        $config = new Config(__FILE__, ['superqueuer' => []]);

        $config->getDatabaseConfig();
    }

    /**
     * @covers ::generateAdapterFactory
     * @covers ::getAdapterFactory
     */
    public function testAdapterFactoryCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Amqp\Factory',
            (new Config(__FILE__, []))->getAdapterFactory()
        );
    }

    /**
     * @covers ::generateAdapterFactory
     * @covers ::getAdapterFactory
     */
    public function testAdapterFactoryIsReused()
    {
        $config = new Config(__FILE__, []);

        $this->assertSame(
            $config->getAdapterFactory(),
            $config->getAdapterFactory()
        );
    }

    /**
     * @covers ::generateAdapterFactory
     * @covers ::getAdapterFactory
     */
    public function testTestingAdapterFactoryCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Testing\Factory',
            (new Config(__FILE__, ['adapter_factory' => 'testing']))->getAdapterFactory()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getJobQueueConfig
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
     * @dataProvider configProvider
     */
    public function testJobQueueConfigOptionsArePassedIn($options)
    {
        $config = new Config(__FILE__, $options);

        $job_queue_config = $config->getJobQueueConfig();

        $uniqid = uniqid();
        $this->assertSame(
            $uniqid,
            call_user_func($job_queue_config->getBufferQueueNameFactory(), $uniqid, [], [])
        );
        $this->assertSame(
            $uniqid,
            call_user_func($job_queue_config->getWorkerQueueNameFactory(), $uniqid, [], [])
        );
        $this->assertSame(
            [$uniqid, ['value' => $uniqid]],
            call_user_func($job_queue_config->getJobRunnerFactory(), $uniqid, ['value' => $uniqid], [])
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueConfig
     * @dataProvider configProvider
     * @expectedException Exception
     */
    public function testRequestingUndeclaredBufferConfigThrowsAnException($options)
    {
        $config = new Config(__FILE__, $options);

        $config->getBufferQueueConfig('undeclared');
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueConfig
     * @dataProvider configProvider
     */
    public function testBufferQueueConfigIsComposedOfDefaultsAndSpecifics($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getBufferQueueConfig('default');

        $this->assertEquals(
            [
                'host'                 => $options['queue_defaults']['host'],
                'queue_prefix'         => $options['buffer_queue_defaults']['queue_prefix'],
                'bufferers_per_server' => $options['buffer_queues']['default']['bufferers_per_server'],
            ],
            [
                'host'                 => $queue_config['host'],
                'queue_prefix'         => $queue_config['queue_prefix'],
                'bufferers_per_server' => $queue_config['bufferers_per_server'],
            ]
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueConfig
     * @dataProvider configProvider
     */
    public function testBufferQueueKeyNameIsSet($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getBufferQueueConfig('default');

        $this->assertEquals(
            'default',
            $queue_config['key_name']
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueConfig
     * @dataProvider configProvider
     */
    public function testBufferQueueNameDefaultsToPrefixAndQueueKey($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getBufferQueueConfig('default');

        $this->assertEquals(
            "{$queue_config['queue_prefix']}{$queue_config['key_name']}",
            $queue_config['queue_name']
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueConfig
     * @dataProvider configProvider
     */
    public function testBufferQueueFetchCountIsDefaulted($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getBufferQueueConfig('default');

        $this->assertEquals(
            1,
            $queue_config['fetch_count']
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueConfig
     * @dataProvider configProvider
     * @expectedException Exception
     */
    public function testRequestingUndeclaredWorkerConfigThrowsAnException($options)
    {
        $config = new Config(__FILE__, $options);

        $config->getWorkerQueueConfig('undeclared');
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueConfig
     * @dataProvider configProvider
     */
    public function testWorkerConfigIsComposedOfDefaultsAndSpecifics($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getWorkerQueueConfig('default');

        $this->assertEquals(
            [
                'host'               => $options['queue_defaults']['host'],
                'queue_prefix'       => $options['worker_queue_defaults']['queue_prefix'],
                'workers_per_server' => $options['worker_queues']['default']['workers_per_server'],
            ],
            [
                'host'               => $queue_config['host'],
                'queue_prefix'       => $queue_config['queue_prefix'],
                'workers_per_server' => $queue_config['workers_per_server'],
            ]
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueConfig
     * @dataProvider configProvider
     */
    public function testWorkerKeyNameIsSet($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getWorkerQueueConfig('default');

        $this->assertEquals(
            'default',
            $queue_config['key_name']
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueConfig
     * @dataProvider configProvider
     */
    public function testWorkerQueueNameDefaultsToPrefixAndQueueKey($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getWorkerQueueConfig('default');

        $this->assertEquals(
            "{$queue_config['queue_prefix']}{$queue_config['key_name']}",
            $queue_config['queue_name']
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueConfig
     * @dataProvider configProvider
     */
    public function testWorkerQueueFetchCountIsOne($options)
    {
        $config = new Config(__FILE__, $options);

        $queue_config = $config->getWorkerQueueConfig('default');

        $this->assertEquals(
            1,
            $queue_config['fetch_count']
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

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueNames
     * @covers ::getOption
     */
    public function testWorkerQueueNamesCanBeRetrieved()
    {
        $config = new Config(__FILE__, [
            'worker_queues' => [
                'abc' => [],
                '123' => [],
            ]
        ]);

        $this->assertEquals(
            ['abc', '123'],
            $config->getWorkerQueueNames()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueNames
     * @covers ::getOption
     */
    public function testBufferQueueNamesCanBeRetrieved()
    {
        $config = new Config(__FILE__, [
            'buffer_queues' => [
                'xyz' => [],
                '456' => [],
            ]
        ]);

        $this->assertEquals(
            ['xyz', '456'],
            $config->getBufferQueueNames()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueConfig
     * @covers ::getOption
     * @dataProvider provideQueueConfigs
     * @param array $expected_config
     * @param $queue_name
     * @param array $hodor_config
     * @throws Exception
     */
    public function testQueueConfigCanBeGenerated(array $expected_config, $queue_name, array $hodor_config)
    {
        $config = new Config(__FILE__, $hodor_config);

        $this->assertEquals($expected_config, $config->getQueueConfig($queue_name));
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueConfig
     * @expectedException Exception
     */
    public function testQueueConfigForUnknownConfigThrowsAnException()
    {
        $config = new Config(__FILE__, ['worker_queues' => []]);
        $config->getQueueConfig('worker-missing');
    }

    /**
     * @return array
     */
    public function provideQueueConfigs()
    {
        return require __DIR__ . '/ConfigTest.queue-config.dataset.php';
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
                'queue_defaults' => [
                    'host' => 'queue-default-host',
                ],
                'buffer_queue_defaults' => [
                    'queue_prefix' => 'buffer-queue-default-prefix',
                ],
                'buffer_queues' => [
                    'default' => [
                        'bufferers_per_server' => 5,
                    ],
                ],
                'worker_queue_defaults' => [
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
