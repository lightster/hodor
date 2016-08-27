<?php

namespace Hodor\JobQueue\Config;

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config\JobQueueConfig
 */
class JobQueueConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getJobRunnerFactory
     * @expectedException Exception
     */
    public function testAJobRunnerFactoryMustBeConfigured()
    {
        $config = new JobQueueConfig([]);

        $config->getJobRunnerFactory();
    }

    /**
     * @covers ::__construct
     * @covers ::getJobRunnerFactory
     * @expectedException Exception
     */
    public function testTheJobRunnerFactoryMustBeACallable()
    {
        $config = new JobQueueConfig([
            'job_runner' => 'blah',
        ]);

        $config->getJobRunnerFactory();
    }

    /**
     * @covers ::__construct
     * @covers ::getJobRunnerFactory
     * @dataProvider configProvider
     */
    public function testTheJobRunnerFactoryIsReturnedIfProperlyConfigured($options)
    {
        $config = new JobQueueConfig($options);

        $callback = $config->getJobRunnerFactory();

        $this->assertTrue(is_callable($callback));
        $this->assertSame(
            $options['job_runner'],
            $callback
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueName
     * @covers ::<private>
     * @expectedException \Exception
     */
    public function testWorkerQueueNameFactoryThrowsExceptionIfItIsNotCallable()
    {
        $config = new JobQueueConfig([
            'worker_queue_name_factory' => 'blah',
        ]);

        $config->getWorkerQueueName('job-worker', [], []);
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueName
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testWorkerQueueNameFactoryIsDefaultedToQueueNameOptionsCallback($options)
    {
        unset($options['worker_queue_name_factory']);
        $config = new JobQueueConfig($options);

        $this->assertEquals(
            'default',
            $config->getWorkerQueueName('n/a', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'other',
            $config->getWorkerQueueName('n/a', [], ['queue_name' => 'other'])
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueName
     * @covers ::<private>
     * @dataProvider configProvider
     * @expectedException \Exception
     */
    public function testDefaultWorkerQueueNameThrowsAnExceptionIfQueueNameIsNotProvided($options)
    {
        unset($options['worker_queue_name_factory']);
        $config = new JobQueueConfig($options);

        $this->assertEquals(
            'other',
            $config->getWorkerQueueName('n/a', [], ['not_queue_name' => 'other'])
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueName
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testWorkerQueueNameFactoryCanBeProvided($options)
    {
        $config = new JobQueueConfig($options);

        $this->assertEquals(
            'non-default',
            $config->getWorkerQueueName('non-default', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'another',
            $config->getWorkerQueueName('another', [], ['queue_name' => 'other'])
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueName
     * @covers ::<private>
     * @expectedException \Exception
     */
    public function testBufferQueueNameFactoryThrowsExceptionIfItIsNotCallable()
    {
        $config = new JobQueueConfig([
            'buffer_queue_name_factory' => 'blah',
        ]);

        $config->getBufferQueueName('buffer-worker', [], []);
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueName
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testBufferQueueNameFactoryIsDefaultedToDefaultQueue($options)
    {
        unset($options['buffer_queue_name_factory']);
        $config = new JobQueueConfig($options);

        $this->assertEquals(
            'default',
            $config->getBufferQueueName('n/a', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'default',
            $config->getBufferQueueName('n/a', [], ['queue_name' => 'other'])
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueName
     * @covers ::<private>
     * @dataProvider configProvider
     */
    public function testBufferQueueNameFactoryCanBeProvided($options)
    {
        $config = new JobQueueConfig($options);

        $this->assertEquals(
            'non-default',
            $config->getBufferQueueName('non-default', [], ['queue_name' => 'default'])
        );
        $this->assertEquals(
            'another',
            $config->getBufferQueueName('another', [], ['queue_name' => 'other'])
        );
    }

    public function configProvider()
    {
        return [
            [[
                'worker_queue_name_factory' => function($name, $params, $options) {
                    return $name;
                },
                'buffer_queue_name_factory' => function($name, $params, $options) {
                    return $name;
                },
                'job_runner' => function($name, $params) {
                    return [$name, $params];
                },
            ]],
        ];
    }
}
