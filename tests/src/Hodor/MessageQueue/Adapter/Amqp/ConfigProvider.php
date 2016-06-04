<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

class ConfigProvider
{
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
