<?php

namespace Hodor\Database\Adapter\TestUtil;

use Hodor\Database\Adapter\FactoryInterface;

class ScenarioCreator
{
    /**
     * @param FactoryInterface $factory
     * @param array $buffered_jobs
     * @param array $queued_jobs
     * @return array
     */
    public function createScenario(FactoryInterface $factory, array $buffered_jobs, array $queued_jobs)
    {
        $uniqid = uniqid();
        return [
            'uniqid'        => $uniqid,
            'queued_jobs'   => $this->queueJobs($factory, $uniqid, $queued_jobs),
            'buffered_jobs' => $this->bufferJobs($factory, $uniqid, $buffered_jobs),
        ];
    }

    /**
     * @param FactoryInterface $factory
     * @param string $uniqid
     * @param array $jobs
     * @return array
     */
    private function bufferJobs(FactoryInterface $factory, $uniqid, array $jobs)
    {
        $buffered_at = date('c', time() - 3600);

        foreach ($jobs as $job) {
            $options = [];
            if (isset($job['run_after'])) {
                $options['run_after'] = date('c', time() + $job['run_after']);
            }
            if (isset($job['job_rank'])) {
                $options['job_rank'] = $job['job_rank'];
            }
            if (isset($job['mutex_id'])) {
                $options['mutex_id'] = "mutex-{$uniqid}-{$job['mutex_id']}";
            }

            $factory->getBufferWorker()->bufferJob(
                'fast-jobs',
                [
                    'name'   => "job-{$uniqid}-{$job['name']}",
                    'params' => [
                        'value' => $uniqid,
                    ],
                    'options' => $options,
                    'meta'   => [
                        'buffered_at'   => $buffered_at,
                        'buffered_from' => "host-{$uniqid}-{$job['name']}",
                    ],
                ]
            );
        }
    }

    /**
     * @param FactoryInterface $factory
     * @param string $uniqid
     * @param array $jobs
     * @return array
     */
    private function queueJobs(FactoryInterface $factory, $uniqid, array $jobs)
    {
        $this->bufferJobs($factory, $uniqid, $jobs);

        $jobs_queued = [];
        foreach ($factory->getSuperqueuer()->getJobsToRunGenerator() as $job) {
            $jobs_queued[] = $factory->getSuperqueuer()->markJobAsQueued($job);
        }

        return $jobs_queued;
    }
}
