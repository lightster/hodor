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
     * @param Message $message
     */
    public function acknowledgeMessage(Message $message);
}
