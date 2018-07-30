<?php

namespace Hodor\Command;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\Command\Arguments
 */
class ArgumentsTest extends TestCase
{
    /**
     * @covers ::setCliOptsLoader
     * @covers ::getConfigFile
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingConfigWhenNotProvidedThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([]);

        $arguments->getConfigFile();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getConfigFile
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingConfigWhenBlankThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([
            'config' => null,
        ]);

        $arguments->getConfigFile();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getConfigFile
     * @covers ::<private>
     */
    public function testRetrievingConfigWhenProvidedWithLongOpt()
    {
        $config_path = 'config.php';
        $arguments = $this->getArgumentsObject([
            'config' => $config_path,
        ]);

        $this->assertEquals(
            $config_path,
            $arguments->getConfigFile()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getConfigFile
     * @covers ::<private>
     */
    public function testRetrievingConfigWhenProvidedWithShortOpt()
    {
        $config_path = 'config.php';
        $arguments = $this->getArgumentsObject([
            'c' => $config_path,
        ]);

        $this->assertEquals(
            $config_path,
            $arguments->getConfigFile()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getQueueName
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingQueueNameWhenNotProvidedThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([]);

        $arguments->getQueueName();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getQueueName
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingQueueNameWhenBlankThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([
            'queue' => null,
        ]);

        $arguments->getQueueName();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getQueueName
     * @covers ::<private>
     */
    public function testRetrievingQueueNameWhenProvidedWithLongOpt()
    {
        $queue_name = uniqid();
        $arguments = $this->getArgumentsObject([
            'queue' => $queue_name,
        ]);

        $this->assertEquals(
            $queue_name,
            $arguments->getQueueName()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getQueueName
     * @covers ::<private>
     */
    public function testRetrievingQueueNameWhenProvidedWithShortOpt()
    {
        $queue_name = uniqid();
        $arguments = $this->getArgumentsObject([
            'q' => $queue_name,
        ]);

        $this->assertEquals(
            $queue_name,
            $arguments->getQueueName()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::isJson
     * @covers ::<private>
     */
    public function testRetrievingIsJsonWhenProvided()
    {
        $arguments = $this->getArgumentsObject([
            'json' => false,
        ]);

        $this->assertTrue(
            $arguments->isJson()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::isJson
     * @covers ::<private>
     */
    public function testRetrievingIsJsonWhenNotProvided()
    {
        $arguments = $this->getArgumentsObject([]);

        $this->assertFalse(
            $arguments->isJson()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobName
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingJobNameWhenNotProvidedThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([]);

        $arguments->getJobName();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobName
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingJobNameWhenBlankThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([
            'job-name' => null,
        ]);

        $arguments->getJobName();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobName
     * @covers ::<private>
     */
    public function testRetrievingJobNameWhenProvidedWithLongOpt()
    {
        $job_name = uniqid();
        $arguments = $this->getArgumentsObject([
            'job-name' => $job_name,
        ]);

        $this->assertEquals(
            $job_name,
            $arguments->getJobName()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobParams
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingJobParamsWhenNotProvidedThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([]);

        $arguments->getJobParams();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobParams
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingJobParamsWhenBlankThrowsAnException()
    {
        $arguments = $this->getArgumentsObject([
            'job-params' => null,
        ]);

        $arguments->getJobParams();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobParams
     * @covers ::<private>
     */
    public function testRetrievingJobParamsWhenStringIsProvided()
    {
        $job_params = uniqid();
        $arguments = $this->getArgumentsObject([
            'job-params' => json_encode($job_params),
        ]);

        $this->assertEquals(
            $job_params,
            $arguments->getJobParams()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobParams
     * @covers ::<private>
     */
    public function testRetrievingJobParamsWhenNullIsProvided()
    {
        $job_params = null;
        $arguments = $this->getArgumentsObject([
            'job-params' => json_encode($job_params),
        ]);

        $this->assertNull(
            $arguments->getJobParams()
        );
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getJobParams
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testRetrievingJobParamsWhenInvalidJsonIsProvided()
    {
        $arguments = $this->getArgumentsObject([
            'job-params' => 'invalid',
        ]);

        $arguments->getJobParams();
    }

    /**
     * @covers ::setCliOptsLoader
     * @covers ::getConfigFile
     * @covers ::getQueueName
     * @covers ::isJson
     * @covers ::<private>
     */
    public function testMultipleArgumentsCanBeRetrieved()
    {
        $config_path = 'config2.php';
        $queue_name = uniqid();
        $arguments = $this->getArgumentsObject([
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
    private function getArgumentsObject(array $return_value)
    {
        $arguments = new Arguments();
        $arguments->setCliOptsLoader(function () use ($return_value) {
            return $return_value;
        });

        return $arguments;
    }
}
