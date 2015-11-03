<?php

namespace Hodor\MessageQueue;

use Exception;
use PHPUnit_Framework_TestCase;

class QueueFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var QueueFactory
     */
    private $queue_factory;

    public function setUp()
    {
        parent::setUp();

        $config_path = __DIR__ . '/../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $this->config = require $config_path;
        $this->queue_factory = new QueueFactory();
    }

    public function testQueueCanBeGenerated()
    {
        $config_template = $this->config['test']['rabbitmq'];
        $config = [
            'host'        => $config_template['host'],
            'port'        => $config_template['port'],
            'username'    => $config_template['username'],
            'password'    => $config_template['password'],
            'queue_name'  => $config_template['queue_prefix'] . uniqid(),
            'fetch_count' => 1,
        ];

        $this->assertInstanceOf(
            '\Hodor\MessageQueue\Queue',
            $this->queue_factory->getQueue($config)
        );
    }
}
