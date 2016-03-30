<?php

namespace Hodor\MessageQueue\Adapter;

interface ProducerInterface
{
    /**
     * @param MessageInterface $message
     */
    public function produceMessage(MessageInterface $message);

    /**
     * @param MessageInterface[] $messages
     */
    public function produceMessageBatch(array $messages);

    /**
     * @param mixed $message
     * @return MessageInterface
     */
    public function generateMessage($message);
}
