<?php

namespace Hodor\JobQueue\Config;

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config\QueueConfig
 */
class QueueConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getMessageQueueConfig
     * @covers ::<private>
     * @dataProvider provideQueueConfigs
     * @param array $expected_config
     * @param string $queue_name
     * @param array $hodor_config
     * @throws Exception
     */
    public function testMessageQueueConfigCanBeGenerated(array $expected_config, $queue_name, array $hodor_config)
    {
        $config = new QueueConfig($hodor_config);

        $this->assertEquals(
            [
                'host'                     => $expected_config['host'],
                'port'                     => $expected_config['port'],
                'username'                 => $expected_config['username'],
                'password'                 => $expected_config['password'],
                'queue_name'               => $expected_config['queue_name'],
                'fetch_count'              => $expected_config['fetch_count'],
            ],
            $config->getMessageQueueConfig($queue_name)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getMessageQueueConfig
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testMessageQueueConfigForUnknownConfigThrowsAnException()
    {
        $config = new QueueConfig(['worker_queues' => []]);
        $config->getMessageQueueConfig('worker-missing');
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerConfig
     * @covers ::<private>
     * @dataProvider provideQueueConfigs
     * @param array $expected_config
     * @param string $queue_name
     * @param array $hodor_config
     * @throws Exception
     */
    public function testWorkerConfigCanBeGenerated(array $expected_config, $queue_name, array $hodor_config)
    {
        $config = new QueueConfig($hodor_config);

        $this->assertEquals(
            [
                'queue_type'    => $expected_config['queue_type'],
                'key_name'      => $expected_config['key_name'],
                'process_count' => $expected_config['process_count'],
            ],
            $config->getWorkerConfig($queue_name)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerConfig
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testWorkerConfigForUnknownConfigThrowsAnException()
    {
        $config = new QueueConfig(['worker_queues' => []]);
        $config->getWorkerConfig('worker-missing');
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueNames
     * @covers ::<private>
     */
    public function testQueueNamesCanBeRetrieved()
    {
        $config = $this->getSimpleQueueConfig();

        $this->assertSame(
            [
                'superqueuer-default' => 'superqueuer-default',
                'bufferer-abc'        => 'bufferer-abc',
                'bufferer-123'        => 'bufferer-123',
                'worker-xyz'          => 'worker-xyz',
                'worker-456'          => 'worker-456',
            ],
            $config->getQueueNames()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::hasWorkerConfig
     * @covers ::<private>
     */
    public function testWorkerExistenceCanBeChecked()
    {
        $queue_config = $this->getSimpleQueueConfig();

        $this->assertTrue($queue_config->hasWorkerConfig('worker', 'xyz'));
        $this->assertFalse($queue_config->hasWorkerConfig('worker', 'abc'));
        $this->assertTrue($queue_config->hasWorkerConfig('bufferer', 'abc'));
        $this->assertFalse($queue_config->hasWorkerConfig('bufferer', 'xyz'));
    }

    /**
     * @covers ::__construct
     * @covers ::hasWorkerConfig
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testCheckingExistenceOfWorkerOfUnknownTypeThrowsAnException()
    {
        $queue_config = $this->getSimpleQueueConfig();

        $this->assertTrue($queue_config->hasWorkerConfig('destructive', 'job-worker'));
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueNames
     * @covers ::getWorkerConfig
     * @covers ::<private>
     */
    public function testQueuesCanBeIteratedThrough()
    {
        $config = $this->getSimpleQueueConfig();

        $process_count = 0;
        foreach ($config->getQueueNames() as $queue_name) {
            $worker_config = $config->getWorkerConfig($queue_name);
            $this->assertSame(++$process_count, $worker_config['process_count']);
        }
    }

    /**
     * @return array
     */
    public function provideQueueConfigs()
    {
        return require __DIR__ . '/../ConfigTest.queue-config.dataset.php';
    }

    /**
     * @return QueueConfig
     */
    private function getSimpleQueueConfig()
    {
        return new QueueConfig([
            'buffer_queues' => [
                'abc' => ['bufferers_per_server' => 2],
                '123' => ['bufferers_per_server' => 3],
            ],
            'worker_queues' => [
                'xyz' => ['workers_per_server' => 4],
                '456' => ['workers_per_server' => 5],
            ],
        ]);
    }
}
