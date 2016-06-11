<?php

namespace Hodor\MessageQueue;

use RuntimeException;

class OutgoingMessage
{
    /**
     * @var mixed
     */
    private $content;

    /**
     * @var string
     */
    private $encoded_content;

    /**
     * @param mixed $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getEncodedContent()
    {
        if (null !== $this->encoded_content) {
            return $this->encoded_content;
        }

        $this->encoded_content = json_encode($this->content, JSON_FORCE_OBJECT, 100);
        if (false === $this->encoded_content) {
            throw new RuntimeException("Failed to json_encode message.");
        }

        return $this->encoded_content;
    }
}
