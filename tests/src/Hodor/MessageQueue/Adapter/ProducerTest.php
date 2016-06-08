<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\Message;
use PHPUnit_Framework_TestCase;

abstract class ProducerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::produceMessage
     * @covers ::<private>
     */
    public function testMessageCanBeProduced()
    {
        $unique_message = 'hello ' . uniqid();

        $this->getTestProducer()->produceMessage(json_encode($unique_message));

        $this->assertSame($unique_message, $this->consumeMessage());
    }

    /**
     * @covers ::__construct
     * @covers ::produceMessageBatch
     * @covers ::<private>
     */
    public function testMessagesCanBeBatchProduced()
    {
        $unique_messages = [
            'hello ' . uniqid(),
            'goodbye ' . uniqid(),
        ];

        $this->getTestProducer()->produceMessageBatch(array_map(function ($value) {
            return json_encode($value);
        }, $unique_messages));

        foreach ($unique_messages as $unique_message) {
            $this->assertSame($unique_message, $this->consumeMessage());
        }
    }

    /**
     * @param array $config_overrides
     * @return ProducerInterface
     */
    abstract protected function getTestProducer(array $config_overrides = []);

    /**
     * @return string $expected_message
     */
    abstract protected function consumeMessage();
}
