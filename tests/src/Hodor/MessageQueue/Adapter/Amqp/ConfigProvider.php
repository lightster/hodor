<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\Testing\Config;
use PHPUnit_Framework_TestCase;

class ConfigProvider
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $test_case;

    /**
     * @param PHPUnit_Framework_TestCase $test_case
     */
    public function __construct(PHPUnit_Framework_TestCase $test_case)
    {
        $this->test_case = $test_case;
    }

    /**
     * @param array $queues
     * @param array $config_overrides
     * @return Config
     */
    public function getConfigAdapter(array $queues, array $config_overrides = [])
    {
        $config = new Config([]);
        foreach ($queues as $queue_key => $queue_config) {
            $config->addQueueConfig($queue_key, array_merge($queue_config, $config_overrides));
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getQueueConfig()
    {
        $rabbit_credentials = $this->getRabbitCredentials();

        return [
            'host'       => $rabbit_credentials['host'],
            'port'       => $rabbit_credentials['port'],
            'username'   => $rabbit_credentials['username'],
            'password'   => $rabbit_credentials['password'],
            'queue_name' => $rabbit_credentials['queue_prefix'] . uniqid(),
        ];
    }

    /**
     * @return array
     */
    private function getRabbitCredentials()
    {
        $config = require __DIR__ . '/../../../../../../config/config.test.php';

        return  $config['test']['rabbitmq'];
    }
}
