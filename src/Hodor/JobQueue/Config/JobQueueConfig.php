<?php

namespace Hodor\JobQueue\Config;

use Exception;

class JobQueueConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge(
            [
                'job_runner'                => null,
                'worker_queue_name_factory' => null,
                'buffer_queue_name_factory' => null,
            ],
            $config
        );
    }

    /**
     * @return callable
     * @throws Exception
     */
    public function getJobRunnerFactory()
    {
        $job_runner = $this->config['job_runner'];

        if (empty($job_runner)) {
            throw new Exception("The 'job_runner' config parameter is required.");
        } elseif (!is_callable($job_runner)) {
            throw new Exception(
                "The provided 'job_runner' config value is not a callable."
            );
        }

        return $job_runner;
    }

    /**
     * @return callable
     * @throws Exception
     */
    public function getWorkerQueueNameFactory()
    {
        $worker_qname_factory = $this->config['worker_queue_name_factory'];

        if (empty($worker_qname_factory)) {
            $worker_qname_factory = function ($name, $params, $options) {
                unset($name, $params);
                if (empty($options['queue_name'])) {
                    throw new Exception(
                        "Job option 'queue_name' is required when using the "
                        . "default queue name factory."
                    );
                }
                return $options['queue_name'];
            };
        } elseif (!is_callable($worker_qname_factory)) {
            throw new Exception(
                "The provided 'worker_queue_name_factory' config value is not a callable."
            );
        }

        return $worker_qname_factory;
    }

    /**
     * @return callable
     * @throws Exception
     */
    public function getBufferQueueNameFactory()
    {
        $buffer_qname_factory = $this->config['buffer_queue_name_factory'];

        if (empty($buffer_qname_factory)) {
            $buffer_qname_factory = function ($name, $params, $options) {
                unset($name, $params, $options);
                return 'default';
            };
        } elseif (!is_callable($buffer_qname_factory)) {
            throw new Exception(
                "The provided 'buffer_queue_name_factory' config value is not a callable."
            );
        }

        return $buffer_qname_factory;
    }
}
