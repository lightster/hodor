<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\OutgoingMessage;

interface ProducerInterface
{
    /**
     * @param OutgoingMessage $message
     * @return
     */
    public function produceMessage(OutgoingMessage $message);

    /**
     * @param OutgoingMessage[] $messages
     */
    public function produceMessageBatch(array $messages);
}
