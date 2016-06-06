<?php

namespace Hodor\JobQueue\JobOptions;

use DateTime;
use Exception;
use Hodor\JobQueue\Config;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\JobOptions\Validator
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testUnknownOptionThrowsAnException()
    {
        $this->generateValidator()->validateJobOptions(['made_up' => 'option']);
    }

    /**
     * @expectedException \Exception
     */
    public function testUnknownQueueNameThrowsAnException()
    {
        $this->generateValidator()->validateJobOptions(['queue_name' => 'made_up_queue']);
    }

    public function testKnownQueueNameCanValidateWithoutAnException()
    {
        $this->generateValidator()->validateJobOptions(['queue_name' => 'queue_a']);
    }

    /**
     * @expectedException \Exception
     */
    public function testRunAfterThrowsAnExceptionIfItIsNotADateTimeObject()
    {
        $this->generateValidator()->validateJobOptions(['run_after' => '2015-12-12']);
    }

    public function testRunAfterCanValidateWithoutAnException()
    {
        $this->generateValidator()->validateJobOptions(['run_after' => new DateTime()]);
    }

    /**
     * @expectedException \Exception
     */
    public function testJobRankThrowsAnExceptionIfANonIntegerIsPassedIn()
    {
        $this->generateValidator()->validateJobOptions(['job_rank' => 'abc']);
    }

    /**
     * @expectedException \Exception
     */
    public function testJobRankThrowsAnExceptionIfAnOutOfRangeRankIsUsed()
    {
        $this->generateValidator()->validateJobOptions(['job_rank' => 25]);
    }

    public function testJobRankCanValidateWithoutAnException()
    {
        $this->generateValidator()->validateJobOptions(['job_rank' => 5]);
    }

    /**
     * @expectedException \Exception
     */
    public function testMutexIdThrowsAnExceptionIfMutexIsNotAScalar()
    {
        $this->generateValidator()->validateJobOptions(['mutex_id' => new \stdClass()]);
    }

    /**
     * @expectedException \Exception
     */
    public function testMutexIdThrowsAnExceptionIfMutexIsEmpty()
    {
        $this->generateValidator()->validateJobOptions(['mutex_id' => '']);
    }

    public function testMutexIdCanValidateWithoutAnException()
    {
        $this->generateValidator()->validateJobOptions(['mutex_id' => 'yay']);
    }

    /**
     * @param Config|null $config
     * @return Validator
     */
    private function generateValidator(Config $config = null)
    {
        if (!$config) {
            $config = new Config(__FILE__, [
                'worker_queues' => [
                    'queue_a' => ['workers_per_server' => 5],
                    'queue_b' => ['workers_per_server' => 5],
                ]
            ]);
        }

        return new Validator($config);
    }
}
