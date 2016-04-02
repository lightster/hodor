<?php

namespace Hodor\MessageQueue\Adapter;

interface FactoryInterface
{
    /**
     * @param string $queue_name
     * @return ConsumerInterface
     */
    public function getConsumer($queue_name);

    /**
     * @param string $queue_name
     * @return ProducerInterface
     */
    public function getProducer($queue_name);
}
