<?php

namespace Hodor\MessageQueue\Adapter;

interface ProducerInterface
{
    /**
     * @param string $message
     */
    public function produceMessage($message);

    /**
     * @param string[] $messages
     */
    public function produceMessageBatch(array $messages);
}
