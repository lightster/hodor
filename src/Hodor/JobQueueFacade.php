<?php

namespace Hodor;

class JobQueueFacade
{
    /**
     * @var \Hodor\BufferQueue
     */
    private static $buffer_queue;

    /**
     * @param string $name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $options the options to use when running the job
     */
    public static function push($name, array $params = [], array $options = [])
    {
        self::getBufferQueue()->push(
            $name,
            $params,
            $options
        );
    }

    /**
     * @param \Hodor\BufferQueue $buffer_queue [description]
     */
    public static function setBufferQueue(BufferQueue $buffer_queue)
    {
        self::$buffer_queue = $buffer_queue;
    }

    /**
     * @return \Hodor\BufferQueue
     */
    private static function getBufferQueue()
    {
        if (self::$buffer_queue) {
            return self::$buffer_queue;
        }

        self::$buffer_queue = new BufferQueue();

        return self::$buffer_queue;
    }
}
