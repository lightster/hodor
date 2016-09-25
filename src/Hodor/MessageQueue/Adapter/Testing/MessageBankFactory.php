<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Exception;
use Hodor\MessageQueue\Adapter\ConfigInterface;

class MessageBankFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var MessageBank[]
     */
    private $message_banks = [];

    /**
     * @return ConfigInterface
     * @throws Exception
     */
    public function getConfig()
    {
        if (!$this->config) {
            throw new Exception(
                'A ConfigInterface must be provided to the MessageBankFactory via setConfig()'
            );
        }

        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $queue_key
     * @return MessageBank
     */
    public function getMessageBank($queue_key)
    {
        if (!empty($this->message_banks[$queue_key])) {
            return $this->message_banks[$queue_key];
        }

        $this->message_banks[$queue_key] = new MessageBank($this->getConfig()->getQueueConfig($queue_key));

        return $this->message_banks[$queue_key];
    }
}
