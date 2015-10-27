<?php

namespace Hodor\JobQueue;

use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
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
     * @dataProvider configProvider
     */
    public function testDatabaseConfigCanBeRetrieved($options)
    {
        $config = new Config(__FILE__, $options);

        $db_config = $config->getDatabaseConfig();

        $this->assertEquals(
            [
                'type' => $options['superqueuer']['database']['type'],
                'dsn'  => $options['superqueuer']['database']['dsn'],
            ],
            [
                'type' => $db_config['type'],
                'dsn'  => $db_config['dsn'],
            ]
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testRetrievingDatabaseConfigThrowsExceptionIfSuperqueuerConfigIsNotDefined()
    {
        $config = new Config(__FILE__, []);

        $db_config = $config->getDatabaseConfig();
    }

    /**
     * @expectedException \Exception
     */
    public function testRetrievingDatabaseConfigThrowsExceptionIfDbConfigIsNotDefined()
    {
        $config = new Config(__FILE__, ['superqueuer' => []]);

        $db_config = $config->getDatabaseConfig();
    }

    /**
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
     * @expectedException \Exception
     */
    public function testWorkerQueueNameFactoryThrowsExceptionIfItIsNotCallable()
    {
        $config = new Config(__FILE__, [
            'worker_queue_name_factory' => 'blah',
        ]);

        $callback = $config->getWorkerQueueNameFactory();
    }

    /**
     * @dataProvider configProvider
     */
    public function testWorkerQueueNameFactoryIsDefaultedToQueueNameOptionsCallback($options)
    {
        unset($options['worker_queue_name_factory']);
        $config = new Config(__FILE__, $options);

        $callback = $config->getWorkerQueueNameFactory();

        $this->assertTrue(is_callable($callback));
        $this->assertEquals(
            'default',
            call_user_func($callback, 'n/a', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'other',
            call_user_func($callback, 'n/a', [], ['queue_name' => 'other'])
        );
    }

    /**
     * @dataProvider configProvider
     * @expectedException \Exception
     */
    public function testDefaultWorkerQueueNameThrowsAnExceptionIfQueueNameIsNotProvided($options)
    {
        unset($options['worker_queue_name_factory']);
        $config = new Config(__FILE__, $options);

        $callback = $config->getWorkerQueueNameFactory();

        $this->assertTrue(is_callable($callback));
        $this->assertEquals(
            'other',
            call_user_func($callback, 'n/a', [], ['not_queue_name' => 'other'])
        );
    }

    /**
     * @dataProvider configProvider
     */
    public function testWorkerQueueNameFactoryCanBeProvided($options)
    {
        $config = new Config(__FILE__, $options);

        $callback = $config->getWorkerQueueNameFactory();

        $this->assertTrue(is_callable($callback));
        $this->assertEquals(
            'non-default',
            call_user_func($callback, 'non-default', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'another',
            call_user_func($callback, 'another', [], ['queue_name' => 'other'])
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testBufferQueueNameFactoryThrowsExceptionIfItIsNotCallable()
    {
        $config = new Config(__FILE__, [
            'buffer_queue_name_factory' => 'blah',
        ]);

        $callback = $config->getBufferQueueNameFactory();
    }

    /**
     * @dataProvider configProvider
     */
    public function testBufferQueueNameFactoryIsDefaultedToDefaultQueue($options)
    {
        unset($options['buffer_queue_name_factory']);
        $config = new Config(__FILE__, $options);

        $callback = $config->getBufferQueueNameFactory();

        $this->assertTrue(is_callable($callback));
        $this->assertEquals(
            'default',
            call_user_func($callback, 'n/a', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'default',
            call_user_func($callback, 'n/a', [], ['queue_name' => 'other'])
        );
    }

    /**
     * @dataProvider configProvider
     */
    public function testBufferQueueNameFactoryCanBeProvided($options)
    {
        $config = new Config(__FILE__, $options);

        $callback = $config->getBufferQueueNameFactory();

        $this->assertTrue(is_callable($callback));
        $this->assertEquals(
            'non-default',
            call_user_func($callback, 'non-default', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'another',
            call_user_func($callback, 'another', [], ['queue_name' => 'other'])
        );
    }

    public function configProvider()
    {
        return [
            [[
                'superqueuer' => [
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
            ]],
        ];
    }
}
