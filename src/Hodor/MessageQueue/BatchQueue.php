<?php

namespace Hodor\MessageQueue;

use Exception;

class BatchQueue
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
