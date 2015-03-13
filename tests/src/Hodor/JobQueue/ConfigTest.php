<?php

namespace Hodor\JobQueue;

use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function testDatabaseConfigCanBeRetrieved($options)
    {
        $config = new Config($options);

        $this->assertEquals($options['database'], $config->getDatabaseConfig());
    }

    /**
     * @dataProvider configProvider
     */
    public function testBufferQueueConfigIsComposedOfDefaultsAndSpecifics($options)
    {
        $config = new Config($options);

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
        $config = new Config($options);

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
        $config = new Config($options);

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
        $config = new Config($options);

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
        $config = new Config($options);

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
        $config = new Config($options);

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
        $config = new Config($options);

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
        $config = new Config($options);

        $queue_config = $config->getWorkerQueueConfig('default');

        $this->assertEquals(
            1,
            $queue_config['fetch_count']
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testQueueNameFactoryThrowsExceptionIfItIsNotCallable()
    {
        $config = new Config([
            'queue_name_factory' => 'blah',
        ]);

        $callback = $config->getQueueNameFactory();
    }

    /**
     * @dataProvider configProvider
     */
    public function testQueueNameFactoryIsDefaultedToQueueNameOptionsCallback($options)
    {
        unset($options['queue_name_factory']);
        $config = new Config($options);

        $callback = $config->getQueueNameFactory();

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
     */
    public function testQueueNameFactoryCanBeProvided($options)
    {
        $config = new Config($options);

        $callback = $config->getQueueNameFactory();

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
                'database' => [
                    'username' => 'some_username',
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
                'queue_name_factory' => function($name, $params, $options) {
                    return $name;
                },
            ]],
        ];
    }
}
