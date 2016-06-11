<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\IncomingMessage;

interface ConsumerInterface
{
    /**
     * @param callable $callback
     */
    public function consumeMessage(callable $callback);

    /**
     * @return int
     */
    public function getMaxMessagesPerConsume();

    /**
     * @return int
     */
    public function getMaxTimePerConsume();
}
