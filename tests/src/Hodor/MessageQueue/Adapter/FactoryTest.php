<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\Message;
use PHPUnit_Framework_TestCase;

abstract class FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProducer
     * @covers ::getConsumer
     * @covers ::<private>
     */
    public function testFactoryGeneratesWorkingProducerAndConsumer()
    {
        $unique_message = 'hello ' . uniqid();

        $factory = $this->getTestFactory();

        $factory->getProducer('fast_jobs')->produceMessage(json_encode($unique_message));

        $factory->getConsumer('fast_jobs')->consumeMessage(function (Message $message) use ($unique_message) {
            $this->assertEquals($unique_message, $message->getContent());
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getProducer
     * @covers ::<private>
     */
    public function testProducerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame(
            $factory->getProducer('fast_jobs'),
            $factory->getProducer('fast_jobs')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumer
     * @covers ::<private>
     */
    public function testConsumerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame(
            $factory->getConsumer('fast_jobs'),
            $factory->getConsumer('fast_jobs')
        );
    }

    /**
     * @param array $config_overrides
     * @return FactoryInterface
     */
    abstract protected function getTestFactory(array $config_overrides = []);
}
