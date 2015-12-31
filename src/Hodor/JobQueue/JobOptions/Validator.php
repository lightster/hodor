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
        try {
            $this->config->getWorkerQueueConfig($options['queue_name']);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param array $options
     * @throws Exception
     */
    private function validateRunAfter(array $options)
    {
        if (false !== strtotime($options['run_after'])) {
            return;
        }

        throw new Exception('\'run_after\' must be a date/time string');
    }
}
