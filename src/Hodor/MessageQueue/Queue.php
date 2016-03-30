<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

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
            $this->messages[] = $this->producer->generateMessage($message);
            return;
        }

        $this->producer->produceMessage($this->producer->generateMessage($message));
    }

    /**
     * @param  callable $callback to use for handling the message
     */
    public function consume(callable $callback)
    {
        $this->consumer->consumeMessage($callback);
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
