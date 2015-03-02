<?php

namespace Hodor;

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
                'worker_queue_defaults' => [
                    'queue_prefix' => 'worker-queue-default-prefix',
                ],
                'worker_queues' => [
                    'default' => [
                        'workers_per_server' => 5,
                    ],
                ],
            ]],
        ];
    }
}
