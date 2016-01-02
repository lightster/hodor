<?php

namespace Hodor\JobQueue\JobOptions;

use DateTime;
use Exception;
use Hodor\JobQueue\Config;
use PHPUnit_Framework_TestCase;

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
    public function testRunAfterThrowsAnExceptionIfItIsNotAValidDateTimeString()
    {
        $this->generateValidator()->validateJobOptions(['run_after' => 'haha']);
    }

    public function testRunAfterCanValidateWithoutAnException()
    {
        $this->generateValidator()->validateJobOptions(['run_after' => date('c')]);
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
