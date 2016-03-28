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

        $this->publishBatchedMessages($this->messages);

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
     * @param array $messages
     */
    private function publishBatchedMessages(array $messages)
    {
        $this->producer->produceMessageBatch($messages);
    }

    /**
     * @param $message
     * @return Message
     * @throws Exception
     */
    private function generateMessage($message)
    {
        return new Message($this->generateAmqpMessage($message));
    }

    /**
     * @param $message
     * @return AMQPMessage
     * @throws Exception
     */
    private function generateAmqpMessage($message)
    {
        $json_message = json_encode($message, JSON_FORCE_OBJECT, 100);
        if (false === $json_message) {
            throw new Exception("Failed to json_encode message with name '{$message['name']}'.");
        }

        return new AMQPMessage(
            $json_message,
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );
    }
}
