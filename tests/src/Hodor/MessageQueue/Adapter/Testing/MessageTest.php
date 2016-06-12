<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\MessageTest as BaseMessageTest;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Message
 */
class MessageTest extends BaseMessageTest
{
    /**
     * @param string $body
     * @return Message
     */
    protected function getBasicMessage($body)
    {
        return new Message($body, new MessageBank(), '');
    }

    /**
     * @return Message
     */
    protected function getAcknowledgeableMessage()
    {
        $message_id = 'hey_there!';

        $message_bank = $this->getMockBuilder('\Hodor\MessageQueue\Adapter\Testing\MessageBank')
            ->disableOriginalConstructor()
            ->setMethods(['acknowledgeMessage'])
            ->getMock();
        $message_bank->expects($this->once())
            ->method('acknowledgeMessage')
            ->with($message_id);

        return new Message('', $message_bank, $message_id);
    }
}
