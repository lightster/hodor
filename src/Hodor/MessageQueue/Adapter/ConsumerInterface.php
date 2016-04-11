<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\Message;

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
