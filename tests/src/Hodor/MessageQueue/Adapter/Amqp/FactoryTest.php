<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\FactoryTest as BaseFactoryTest;
use Hodor\MessageQueue\Adapter\Testing\Config;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Factory
 */
class FactoryTest extends BaseFactoryTest
{
    /**
     * @var Factory[]
     */
    private $factories;

    /**
     * @var Config
     */
    private $config;

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->factories as $factory) {
            $factory->disconnectAll();
        }
        $this->factories = [];
    }

    /**
     * @covers ::disconnectAll
     */
    public function testDisconnectAllWorksIfFactoryHasNotBeenUsed()
    {
        $this->getTestFactory()->disconnectAll();

        $this->assertTrue(true);
    }

    /**
     * @covers ::disconnectAll
     */
    public function testDisconnectAllWorksAfterUsingFactory()
    {
        $test_factory = $this->getTestFactory();

        $test_factory->getProducer('fast_jobs');
        $test_factory->disconnectAll();

        $this->assertTrue(true);
    }

    /**
     * @param array $config_overrides
     * @return Factory
     */
    protected function getTestFactory(array $config_overrides = [])
    {
        $test_factory = new Factory($this->getTestConfig($config_overrides));

        $this->factories[] = $test_factory;

        return $test_factory;
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
