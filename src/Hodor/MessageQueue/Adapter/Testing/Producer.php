<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\OutgoingMessage;

class Producer implements ProducerInterface
{
    /**
     * @var MessageBank
     */
    private $message_bank;

    /**
     * @param MessageBank $message_bank
     */
    public function __construct(MessageBank $message_bank)
    {
        $this->message_bank = $message_bank;
    }

    /**
     * @param OutgoingMessage $message
     */
    public function produceMessage(OutgoingMessage $message)
    {
        $this->message_bank->produceMessage($message->getEncodedContent());
    }

    /**
     * @param string[] $messages
     */
    public function produceMessageBatch(array $messages)
    {
        foreach ($messages as $message) {
            $this->message_bank->produceMessage($message->getEncodedContent());
        }
    }
}
