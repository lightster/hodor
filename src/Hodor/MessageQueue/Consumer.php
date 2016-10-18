<?php

namespace Hodor\MessageQueue;

use Hodor\MessageQueue\Adapter\FactoryInterface;

class Consumer
{
    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var ConsumerQueue
     */
    private $consumer_queues;

    /**
     * @param FactoryInterface $adapter_factory
     */
    public function __construct(FactoryInterface $adapter_factory)
    {
        $this->adapter_factory = $adapter_factory;
    }

    /**
     * @param string $queue_name
     * @return ConsumerQueue
     */
    public function getQueue($queue_name)
    {
        if (isset($this->consumer_queues[$queue_name])) {
            return $this->consumer_queues[$queue_name];
        }

        $this->checkQueueName($queue_name);

        $this->consumer_queues[$queue_name] = new ConsumerQueue(function (callable $callback) use ($queue_name) {
            $this->consume($queue_name, $callback);
        });

        return $this->consumer_queues[$queue_name];
    }

    /**
     * @param string $queue_name
     * @param callable $callback to use for handling the message
     */
    private function consume($queue_name, callable $callback)
    {
        $start_time = time();
        $message_count = 0;

        $consumer = $this->adapter_factory->getConsumer($queue_name);

        $max_message_count = $consumer->getMaxMessagesPerConsume();
        $max_time = $consumer->getMaxTimePerConsume();

        do {
            $consumer->consumeMessage($callback);
            ++$message_count;
        } while ($message_count < $max_message_count && time() - $start_time <= $max_time);
    }

    /**
     * @param string $queue_name
     */
    private function checkQueueName($queue_name)
    {
        $this->adapter_factory->getConsumer($queue_name);
    }
}
