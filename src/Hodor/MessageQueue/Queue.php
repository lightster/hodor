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
            $this->messages[] = $this->generateMessage($message);
            return;
        }

        $this->producer->produceMessage($this->generateMessage($message));
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

    /**
     * @param mixed $message
     * @return string
     * @throws RuntimeException
     */
    private function generateMessage($message)
    {
        $json_message = json_encode($message, JSON_FORCE_OBJECT, 100);
        if (false === $json_message) {
            throw new RuntimeException("Failed to json_encode message with name '{$message['name']}'.");
        }

        return $json_message;
    }
}
