<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\IncomingMessage;

class Consumer implements ConsumerInterface
{
    /**
     * @var MessageBank
     */
    private $message_bank;

    /**
     * @param MessageBank $message_bank
     */
    public function __construct(MessageBank $message_bank)
    {
        $this->message_bank = $message_bank;
    }

    /**
     * @param callable $callback
     */
    public function consumeMessage(callable $callback)
    {
        $message_adapter = $this->message_bank->consumeMessage();
        $incoming_message = new IncomingMessage($message_adapter);

        $callback($incoming_message);
    }

    /**
     * @return int
     */
    public function getMaxMessagesPerConsume()
    {
        return $this->message_bank->getMaxMessagesPerConsume();
    }

    /**
     * @return int
     */
    public function getMaxTimePerConsume()
    {
        return $this->message_bank->getMaxTimePerConsume();
    }
}
