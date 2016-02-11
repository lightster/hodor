<?php

namespace Hodor\MessageQueue;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Queue
{
    /**
     * @var array
     */
    private $queue_config;

    /**
     * @var AMQPChannel $channel
     */
    private $channel;

    /**
     * @var bool
     */
    private $is_in_transaction = false;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param array $queue_config
     * @param AMQPChannel $channel
     */
    public function __construct(array $queue_config, AMQPChannel $channel)
    {
        $this->queue_config = $queue_config;
        $this->channel = $channel;
    }

    /**
     * @param  mixed $message
     * @throws Exception
     */
    public function push($message)
    {
        if ($this->is_in_transaction) {
            $this->messages[] = $this->generateAmqpMessage($message);
            return;
        }

        $this->channel->basic_publish(
            $this->generateAmqpMessage($message),
            '',
            $this->queue_config['queue_name']
        );
    }

    /**
     * @param  callable $callback to use for handling the message
     */
    public function consume(callable $callback)
    {
        $this->channel->basic_consume(
            $this->queue_config['queue_name'],
            '',
            false,
            ($auto_ack = false),
            false,
            false,
            function ($amqp_message) use ($callback) {
                $message = new Message($amqp_message);
                $callback($message);
            }
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function beginTransaction()
    {
        if ($this->is_in_transaction) {
            throw new Exception("The queue is already in transaction.");
        }

        $this->is_in_transaction = true;
    }

    public function commitTransaction()
    {
        if (!$this->is_in_transaction) {
            throw new Exception("The queue is not in transaction.");
        }

        $this->publishBatch($this->messages);

        $this->is_in_transaction = false;
        $this->messages = [];
    }

    public function rollbackTransaction()
    {
        if (!$this->is_in_transaction) {
            throw new Exception("The queue is not in transaction.");
        }

        $this->is_in_transaction = false;
        $this->messages = [];
    }

    /**
     * @param array $messages
     */
    private function publishBatch(array $messages)
    {
        if (count($this->messages) == 0) {
            return;
        }

        foreach ($this->messages as $message) {
            $this->channel->batch_basic_publish(
                $message,
                '',
                $this->queue_config['queue_name']
            );
        }
        $this->channel->publish_batch();
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
