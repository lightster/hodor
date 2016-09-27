<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Amqp\Factory as AmqpFactory;
use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\Testing\Factory as TestingFactory;
use Hodor\MessageQueue\Adapter\FactoryInterface;

class AdapterFactory
{
    /**
     * @param ConfigInterface $mq_config
     * @return FactoryInterface
     * @throws Exception
     */
    public function getAdapter(ConfigInterface $mq_config)
    {
        $config = $mq_config->getAdapterFactoryConfig();

        if (empty($config['type'])) {
            throw new Exception(
                "The message queue 'type' must provided in connection config."
            );
        }

        if ('testing' === $config['type']) {
            return $this->getTestingFactory($mq_config, $config);
        }

        if ('amqp' === $config['type']) {
            return new AmqpFactory($mq_config);
        }

        throw new Exception("A message queue adapter factory is not associated with '{$config['type']}'.");
    }

    /**
     * @param ConfigInterface $mq_config
     * @param array $config
     * @return TestingFactory
     */
    private function getTestingFactory(ConfigInterface $mq_config, array $config)
    {
        $message_bank_factory = null;
        extract($config, EXTR_IF_EXISTS);

        return new TestingFactory($mq_config, $message_bank_factory);
    }
}
