<?php

namespace Hodor\JobQueue\JobOptions;

use DateTime;
use Exception;
use Hodor\JobQueue\Config;

class Validator
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $validators = [
        'queue_name' => 'validateQueueName',
        'run_after' => 'validateRunAfter',
        'job_rank' => 'validateJobRank',
        'mutex_id' => 'validateMutexId',
    ];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $options
     * @throws Exception
     */
    public function validateJobOptions(array $options)
    {
        $this->validateOptionsAreKnown($options);

        foreach ($options as $option_name => $option_value) {
            call_user_func(
                [$this, $this->validators[$option_name]],
                $options
            );
        }
    }

    /**
     * @param array $options
     * @throws Exception
     */
    private function validateOptionsAreKnown(array $options)
    {
        $unknown_keys = array_diff(array_keys($options), array_keys($this->validators));

        if (empty($unknown_keys)) {
            return;
        }

        throw new Exception("Unknown option(s) were provided: " . implode(", ", $unknown_keys));
    }

    /**
     * @param array $options
     * @throws Exception
     */
    private function validateQueueName(array $options)
    {
        if ($this->config->getWorkerConfig()->hasWorkerConfig("worker", $options['queue_name'])) {
            return;
        }

        throw new Exception("Worker named '{$options['queue_name']}' not found.");
    }

    /**
     * @param array $options
     * @throws Exception
     */
    private function validateRunAfter(array $options)
    {
        if ($options['run_after'] instanceof DateTime) {
            return;
        }

        throw new Exception('\'run_after\' must be an instance of \DateTime');
    }

    /**
     * @param array $options
     * @throws Exception
     */
    private function validateJobRank(array $options)
    {
        if (is_int($options['job_rank'])
            && -20 <= $options['job_rank'] && $options['job_rank'] <= 19
        ) {
            return;
        }

        throw new Exception('\'job_rank\' must be an integer between -20 and 19');
    }

    /**
     * @param array $options
     * @throws Exception
     */
    private function validateMutexId(array $options)
    {
        if (is_scalar($options['mutex_id']) && strlen("{$options['mutex_id']}") >= 1) {
            return;
        }

        throw new Exception('Custom \'mutex_id\' values must be strings of at least 1 character in length');
    }
}
