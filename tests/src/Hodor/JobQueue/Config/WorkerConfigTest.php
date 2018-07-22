<?php

namespace Hodor\JobQueue\Config;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config\WorkerConfig
 */
class WorkerConfigTest extends TestCase
{
    /**
     * @var QueueConfig
     */
    private $queue_config;

    public function setUp()
    {
        parent::setUp();

        $this->queue_config = new QueueConfig([
            'buffer_queues' => ['buffer-worker' => ['bufferers_per_server' => 15]],
            'worker_queues' => ['job-worker' => ['workers_per_server' => 5]],
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::hasWorkerConfig
     * @covers ::<private>
     */
    public function testWorkerExistenceCanBeChecked()
    {
        $worker_config = new WorkerConfig($this->queue_config);

        $this->assertTrue($worker_config->hasWorkerConfig('bufferer', 'buffer-worker'));
        $this->assertFalse($worker_config->hasWorkerConfig('bufferer', 'non-worker'));
        $this->assertTrue($worker_config->hasWorkerConfig('worker', 'job-worker'));
        $this->assertFalse($worker_config->hasWorkerConfig('worker', 'non-worker'));
    }

    /**
     * @covers ::__construct
     * @covers ::hasWorkerConfig
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testCheckingExistenceOfWorkerOfUnknownTypeThrowsAnException()
    {
        $worker_config = new WorkerConfig($this->queue_config);

        $this->assertTrue($worker_config->hasWorkerConfig('destructive', 'job-worker'));
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerConfigs
     * @covers ::<private>
     */
    public function testWorkerConfigsCanBeRetrieved()
    {
        $worker_config = new WorkerConfig($this->queue_config);
        $codebase_path = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        $base_path = "{$codebase_path}/src/Hodor/JobQueue/Config/../../../../bin";

        $this->assertSame(
            [
                'superqueuer-default' => [
                    'worker_type'   => 'superqueuer',
                    'worker_name'   => 'default',
                    'process_count' => 1,
                    'command'       => "{$base_path}/superqueuer.php",
                ],
                'bufferer-buffer-worker' => [
                    'worker_type'   => 'bufferer',
                    'worker_name'   => 'buffer-worker',
                    'process_count' => 15,
                    'command'       => "{$base_path}/buffer-worker.php",
                ],
                'worker-job-worker' => [
                    'worker_type'   => 'worker',
                    'worker_name'   => 'job-worker',
                    'process_count' => 5,
                    'command'       => "{$base_path}/job-worker.php",
                ],
            ],
            $worker_config->getWorkerConfigs()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerConfigs
     * @covers ::<private>
     */
    public function testWorkerConfigsCanBeRetrievedMultipleTimes()
    {
        $worker_config = new WorkerConfig($this->queue_config);

        $this->assertSame(
            $worker_config->getWorkerConfigs(),
            $worker_config->getWorkerConfigs()
        );
    }
}
