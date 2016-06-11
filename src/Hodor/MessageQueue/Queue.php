<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

class Queue
{
    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var bool
     */
    private $is_in_batch = false;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param ConsumerInterface $consumer
     * @param ProducerInterface $producer
     */
    public function __construct(ConsumerInterface $consumer, ProducerInterface $producer)
    {
        $this->consumer = $consumer;
        $this->producer = $producer;
    }

    /**
     * @param  mixed $message
     * @throws Exception
     */
    public function push($message)
    {
        if ($this->is_in_batch) {
            $this->messages[] = new OutgoingMessage($message);
            return;
        }

        $this->producer->produceMessage(new OutgoingMessage($message));
    }

    /**
     * @param  callable $callback to use for handling the message
     */
    public function consume(callable $callback)
    {
        $start_time = time();
        $message_count = 0;

        $max_message_count = $this->consumer->getMaxMessagesPerConsume();
        $max_time = $this->consumer->getMaxTimePerConsume();

        do {
            $this->consumer->consumeMessage($callback);
            ++$message_count;
        } while ($message_count < $max_message_count && time() - $start_time <= $max_time);
    }

    public function beginBatch()
    {
        if ($this->is_in_batch) {
            throw new Exception("The queue is already in transaction.");
        }

        $this->is_in_batch = true;
    }

    public function publishBatch()
    {
        if (!$this->is_in_batch) {
            throw new Exception("The queue is not in transaction.");
        }

        $this->producer->produceMessageBatch($this->messages);

        $this->is_in_batch = false;
        $this->messages = [];
    }

    public function discardBatch()
    {
        if (!$this->is_in_batch) {
            throw new Exception("The queue is not in transaction.");
        }

        $this->is_in_batch = false;
        $this->messages = [];
    }
}
