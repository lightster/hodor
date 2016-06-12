<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\MessageInterface;

class Message implements MessageInterface
{
    /**
     * @var
     */
    private $body;

    /**
     * @var MessageBank $message_bank
     */
    private $message_bank;

    /**
     * @var string
     */
    private $message_id;

    /**
     * @param string $body
     * @param MessageBank $message_bank
     * @param string $message_id
     */
    public function __construct($body, MessageBank $message_bank, $message_id)
    {
        $this->body = $body;
        $this->message_bank = $message_bank;
        $this->message_id = $message_id;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->body;
    }

    public function acknowledge()
    {
        return $this->message_bank->acknowledgeMessage($this->message_id);
    }
}
