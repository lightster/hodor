<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\FactoryTest as BaseFactoryTest;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Factory
 */
class FactoryTest extends BaseFactoryTest
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param array $config_overrides
     * @return Factory
     */
    protected function getTestFactory(array $config_overrides = [])
    {
        return new Factory($this->getTestConfig($config_overrides));
    }

    /**
     * @param array $config_overrides
     * @return Config
     */
    private function getTestConfig(array $config_overrides = [])
    {
        if ($this->config) {
            return $this->config;
        }

        $config_provider = new ConfigProvider($this);
        $test_queues = $this->getTestQueues($config_provider);
        $this->config = $config_provider->getConfigAdapter($test_queues, $config_overrides);

        return $this->config;
    }

    /**
     * @param ConfigProvider $config_provider
     * @return array
     */
    private function getTestQueues(ConfigProvider $config_provider)
    {
        return [
            'fast_jobs' => $config_provider->getQueueConfig(),
        ];
    }
}
