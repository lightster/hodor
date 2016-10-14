<?php

namespace Hodor\MessageQueue;

class ProducerQueue
{
    /**
     * @var callable
     */
    private $pusher;

    /**
     * @param callable $pusher
     */
    public function __construct(callable $pusher)
    {
        $this->pusher = $pusher;
    }

    /**
     * @param mixed $message
     */
    public function push($message)
    {
        call_user_func($this->pusher, $message);
    }
}
