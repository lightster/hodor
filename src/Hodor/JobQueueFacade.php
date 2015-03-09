<?php

namespace Hodor;

use Hodor\Config\LoaderFactory;
use Hodor\JobQueue\QueueFactory;

class JobQueueFacade
{
    /**
     * @var string
     */
    private static $config_file;

    /**
     * @var \Hodor\Config\LoaderFactory
     */
    private static $config;

    /**
     * @var \Hodor\JobQueue\QueueFactory
     */
    private static $queue_factory;

    /**
     * @param string $queue_name the name of the queue to push the job to
     * @param string $job_name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $options the options to use when running the job
     */
    public static function push($queue_name, $job_name, array $params = [], array $options = [])
    {
        self::getQueueFactory()->getWorkerQueue($queue_name)->push(
            $job_name,
            $params
        );
    }

    /**
     * @param string $config_file
     */
    public static function setConfigFile($config_file)
    {
        self::$config_file = $config_file;
    }

    /**
     * @return \Hodor\Config
     */
    public static function getConfig()
    {
        if (self::$config) {
            return self::$config;
        }

        if (self::$config_file) {
            $config_loader_factory = new LoaderFactory();
            self::$config = $config_loader_factory->loadFromFile(self::$config_file);
        } else {
            throw new Exception(
                "Config could not be found or generated by JobQueueFacade."
            );
        }

        return self::$config;
    }

    /**
     * @param \Hodor\JobQueue\QueueFactory $queue_factory
     */
    public static function setQueueFactory(QueueFactory $queue_factory)
    {
        self::$queue_factory = $queue_factory;
    }

    /**
     * @return \Hodor\JobQueue\WorkerQueue
     */
    private static function getQueueFactory()
    {
        if (self::$queue_factory) {
            return self::$queue_factory;
        }

        self::$queue_factory = new QueueFactory(self::getConfig());

        return self::$queue_factory;
    }
}
