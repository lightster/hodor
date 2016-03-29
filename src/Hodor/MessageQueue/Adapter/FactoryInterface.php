<?php

namespace Hodor\MessageQueue\Adapter;

interface FactoryInterface
{
    /**
     * @param array $queue_config
     * @return ConsumerInterface
     */
    public function getConsumer(array $queue_config);

    /**
     * @param array $queue_config
     * @return ProducerInterface
     */
    public function getProducer(array $queue_config);
}
