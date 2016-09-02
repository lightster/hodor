<?php

namespace Hodor\Database\Adapter\TestUtil;

use Hodor\Database\AdapterInterface;

class ScenarioCreator
{
    /**
     * @param AdapterInterface $db_adapter
     * @param array $buffered_jobs
     * @param array $queued_jobs
     * @return array
     */
    public function createScenario(AdapterInterface $db_adapter, array $buffered_jobs, array $queued_jobs)
    {
        $uniqid = uniqid();
        return [
            'uniqid'        => $uniqid,
            'queued_jobs'   => $this->queueJobs($db_adapter, $uniqid, $queued_jobs),
            'buffered_jobs' => $this->bufferJobs($db_adapter, $uniqid, $buffered_jobs),
        ];
    }

    /**
     * @param AdapterInterface $db_adapter
     * @param string $uniqid
     * @param array $jobs
     * @return array
     */
    private function bufferJobs(AdapterInterface $db_adapter, $uniqid, array $jobs)
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

            $db_adapter->bufferJob(
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
     * @param AdapterInterface $db_adapter
     * @param string $uniqid
     * @param array $jobs
     * @return array
     */
    private function queueJobs(AdapterInterface $db_adapter, $uniqid, array $jobs)
    {
        $this->bufferJobs($db_adapter, $uniqid, $jobs);

        $jobs_queued = [];
        foreach ($db_adapter->getJobsToRunGenerator() as $job) {
            $jobs_queued[] = $db_adapter->markJobAsQueued($job);
        }

        return $jobs_queued;
    }
}
