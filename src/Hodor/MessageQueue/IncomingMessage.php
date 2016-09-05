<?php

namespace Hodor\MessageQueue;

use Hodor\MessageQueue\Adapter\MessageInterface;

class IncomingMessage
{
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var bool
     */
    private $is_loaded;

    /**
     * @var mixed
     */
    private $content;

    /**
     * @var bool
     */
    private $was_acked = false;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if ($this->is_loaded) {
            return $this->content;
        }

        $this->content = json_decode($this->message->getContent(), true);
        $this->is_loaded = true;

        return $this->content;
    }

    public function acknowledge()
    {
        if ($this->was_acked) {
            return;
        }

        $this->message->acknowledge();

        $this->was_acked = true;
    }

    /**
     * @return bool
     */
    public function isAcked()
    {
        return $this->was_acked;
    }
}
