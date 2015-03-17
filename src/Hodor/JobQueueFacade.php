<?php

namespace Hodor;

use Hodor\JobQueue\JobQueue;

class JobQueueFacade
{
    /**
     * @var \Hodor\JobQueue\JobQueue
     */
    private static $job_queue;

    /**
     * @param string $job_name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $options the options to use when running the job
     */
    public static function push($job_name, array $params = [], array $options = [])
    {
        self::getJobQueue()->push($job_name, $params, $options);
    }

    /**
     * @param string $config_file
     */
    public static function setConfigFile($config_file)
    {
        self::getJobQueue()->setConfigFile($config_file);
    }

    /**
     * @param \Hodor\JobQueue\JobQueue $job_queue
     */
    public static function setJobQueue(JobQueue $job_queue)
    {
        self::$job_queue = $job_queue;
    }

    /**
     * @return \Hodor\JobQueue\JobQueue
     */
    private static function getJobQueue()
    {
        if (self::$job_queue) {
            return self::$job_queue;
        }

        self::$job_queue = new JobQueue();

        return self::$job_queue;
    }
}
