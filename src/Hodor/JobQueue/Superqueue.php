<?php

namespace Hodor\JobQueue;

use Hodor\Database\AdapterFactory as DbAdapterFactory;
use Hodor\MessageQueue\Message;

class Superqueue
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var QueueFactory
     */
    private $queue_factory;

    /**
     * @var DbAdapterInterface
     */
    private $database;

    /**
     * @param QueueFactory $queue_factory
     */
    public function __construct(array $config, QueueFactory $queue_factory)
    {
        $this->config = $config;
        $this->queue_factory = $queue_factory;
    }

    /**
     * @param Message $message
     */
    public function bufferJobFromBufferQueueToDatabase(Message $message)
    {
        $db = $this->getDatabase();

        $db->beginTransaction();

        $content = $message->getContent();
        $queue_name = $this->queue_factory->getWorkerQueueNameForJob(
            $content['name'],
            $content['params'],
            $content['options']
        );

        $db->bufferJob($queue_name, [
            'name'    => $content['name'],
            'params'  => $content['params'],
            'options' => $content['options'],
            'meta'    => $content['meta'],
        ]);

        $db->commitTransaction();
        $message->acknowledge();
    }

    /**
     * @return DbAdapterInterface
     */
    private function getDatabase()
    {
        if ($this->database) {
            return $this->database;
        }

        $db_adapter_factory = new DbAdapterFactory($this->config['database']);

        $this->database = $db_adapter_factory->getAdapter($this->config['database']['type']);

        return $this->database;
    }
}
