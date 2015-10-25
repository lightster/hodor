<?php

namespace Hodor\Command;

use Exception;
use PHPUnit_Framework_TestCase;

class QueueFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     */
    public function testRetrievingConfigWhenNotProvidedThrowsAnException()
    {
        $arguments = $this->getMockedArguments([]);

        $arguments->getConfigFile();
    }

    /**
     * @expectedException Exception
     */
    public function testRetrievingConfigWhenBlankThrowsAnException()
    {
        $arguments = $this->getMockedArguments([
            'config' => null,
        ]);

        $arguments->getConfigFile();
    }

    public function testRetrievingConfigWhenProvidedWithLongOpt()
    {
        $config_path = 'config.php';
        $arguments = $this->getMockedArguments([
            'config' => $config_path,
        ]);

        $this->assertEquals(
            $config_path,
            $arguments->getConfigFile()
        );
    }

    public function testRetrievingConfigWhenProvidedWithShortOpt()
    {
        $config_path = 'config.php';
        $arguments = $this->getMockedArguments([
            'c' => $config_path,
        ]);

        $this->assertEquals(
            $config_path,
            $arguments->getConfigFile()
        );
    }
    /**
     * @expectedException Exception
     */
    public function testRetrievingQueueNameWhenNotProvidedThrowsAnException()
    {
        $arguments = $this->getMockedArguments([]);

        $arguments->getQueueName();
    }

    /**
     * @expectedException Exception
     */
    public function testRetrievingQueueNameWhenBlankThrowsAnException()
    {
        $arguments = $this->getMockedArguments([
            'queue' => null,
        ]);

        $arguments->getQueueName();
    }

    public function testRetrievingQueueNameWhenProvidedWithLongOpt()
    {
        $queue_name = uniqid();
        $arguments = $this->getMockedArguments([
            'queue' => $queue_name,
        ]);

        $this->assertEquals(
            $queue_name,
            $arguments->getQueueName()
        );
    }

    public function testRetrievingQueueNameWhenProvidedWithShortOpt()
    {
        $queue_name = uniqid();
        $arguments = $this->getMockedArguments([
            'q' => $queue_name,
        ]);

        $this->assertEquals(
            $queue_name,
            $arguments->getQueueName()
        );
    }

    public function testRetrievingIsJsonWhenProvided()
    {
        $arguments = $this->getMockedArguments([
            'json' => false,
        ]);

        $this->assertTrue(
            $arguments->isJson()
        );
    }

    public function testRetrievingIsJsonWhenNotProvided()
    {
        $arguments = $this->getMockedArguments([]);

        $this->assertFalse(
            $arguments->isJson()
        );
    }

    public function testMultipleArgumentsCanBeRetrieved()
    {
        $config_path = 'config2.php';
        $queue_name = uniqid();
        $arguments = $this->getMockedArguments([
            'config' => $config_path,
            'queue'  => $queue_name,
            'json'   => false,
        ]);

        $this->assertEquals(
            $config_path,
            $arguments->getConfigFile()
        );
        $this->assertEquals(
            $queue_name,
            $arguments->getQueueName()
        );
        $this->assertTrue(
            $arguments->isJson()
        );
    }

    /**
     * @param array $return_value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedArguments(array $return_value)
    {
        $arguments = $this->getMockBuilder('\Hodor\Command\Arguments')
            ->setMethods(['getCliOpts'])
            ->getMock();
        $arguments->expects($this->once())
            ->method('getCliOpts')
            ->will($this->returnValue($return_value));

        return $arguments;
    }
}
