<?php

namespace Hodor\MessageQueue\Adapter\Testing;

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
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
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

        $this->message_banks[$queue_key] = new MessageBank($this->config->getQueueConfig($queue_key));

        return $this->message_banks[$queue_key];
    }
}
