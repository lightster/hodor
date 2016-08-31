<?php

namespace Hodor\Database;

use Generator;
use Hodor\Database\Adapter\FactoryInterface;

class ConverterAdapter implements AdapterInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $queue_name
     * @param array $job
     */
    public function bufferJob($queue_name, array $job)
    {
        $this->factory->getBufferWorker()->bufferJob($queue_name, $job);
    }

    /**
     * @return Generator
     */
    public function getJobsToRunGenerator()
    {
        foreach ($this->factory->getSuperqueuer()->getJobsToRunGenerator() as $job) {
            yield $job;
        }
    }

    /**
     * @param array $job
     * @return array
     */
    public function markJobAsQueued(array $job)
    {
        return $this->factory->getSuperqueuer()->markJobAsQueued($job);
    }

    /**
     * @param array $meta
     */
    public function markJobAsSuccessful(array $meta)
    {
        return $this->factory->getDequeuer()->markJobAsSuccessful($meta);
    }

    /**
     * @param array $meta
     */
    public function markJobAsFailed(array $meta)
    {
        return $this->factory->getDequeuer()->markJobAsFailed($meta);
    }

    public function beginTransaction()
    {
        $this->factory->getSuperqueuer()->beginBatch();
    }

    public function commitTransaction()
    {
        $this->factory->getSuperqueuer()->publishBatch();
    }

    /**
     * @param $category
     * @param $name
     * @return bool
     */
    public function requestAdvisoryLock($category, $name)
    {
        return $this->factory->getSuperqueuer()->requestAdvisoryLock($category, $name);
    }

    /**
     * @return FactoryInterface
     */
    public function getAdapterFactory()
    {
        return $this->factory;
    }
}
