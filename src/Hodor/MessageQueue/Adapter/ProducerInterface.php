<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\Message;

interface ProducerInterface
{
    /**
     * @param Message $message
     */
    public function produceMessage(Message $message);

    /**
     * @param Message[] $messages
     */
    public function produceMessageBatch(array $messages);
}
