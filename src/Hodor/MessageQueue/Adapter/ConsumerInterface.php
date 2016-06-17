<?php

namespace Hodor\MessageQueue\Adapter;

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
