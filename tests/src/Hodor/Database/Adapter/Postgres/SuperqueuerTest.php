<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\Postgres\Factory as PostgresAdapterFactory;
use Hodor\Database\Adapter\SuperqueuerTest as SuperqueuerBaseTest;
use Hodor\Database\Adapter\TestUtil\PostgresProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Postgres\Superqueuer
 */
class SuperqueuerTest extends SuperqueuerBaseTest
{
    /**
     * @covers ::__construct
     * @covers ::markJobAsQueued
     * @covers ::getJobsToRunGenerator
     * @covers ::<private>
     */
    public function testThousandsOfBufferedJobsCanBeHandled()
    {
        $uniqid = uniqid();

        $sql = <<<'SQL'
-- increase worker memory so Postgres can better handle superqueuer query
SET work_mem = '4MB';

INSERT INTO buffered_jobs
(
    queue_name,
    job_name,
    job_params,
    buffered_at,
    buffered_from,
    inserted_from,
    run_after,
    job_rank,
    mutex_id
)
SELECT
    'any_queue' AS queue_name,
    'job-' || :uniqid || '-' || id,
    '{}',
    NOW() - (1000000 - id) * INTERVAL '1 second',
    'here',
    'here',
    NOW() - (1000000 - id) * INTERVAL '1 second',
    ABS(id - 249999),
    :uniqid || MOD(id, 100000)
FROM generate_series(1, 500000) AS series(id)
ORDER BY RANDOM();

INSERT INTO queued_jobs
(
    queue_name,
    job_name,
    job_params,
    buffered_at,
    buffered_from,
    inserted_from,
    run_after,
    job_rank,
    mutex_id,
    buffered_job_id,
    superqueued_from
)
SELECT
    'any_queue' AS queue_name,
    'job-' || :uniqid || '-' || id,
    '{}',
    NOW() - (500000 - id) * INTERVAL '1 second',
    'here',
    'here',
    NOW() - (500000 - id) * INTERVAL '1 second',
    ABS(id - 249999),
    :uniqid || id,
    id,
    'here'
FROM generate_series(0, 250000, 5) AS series(id)
ORDER BY RANDOM();
SQL;

        /**
         * @var $adapter_factory PostgresAdapterFactory
         */
        $adapter_factory = $this->getProvisioner()->getAdapterFactory();
        $adapter_factory->getYoPdo()->queryMultiple($sql, ['uniqid' => $uniqid]);

        $adapter_factory->getYoPdo()->queryMultiple('VACUUM ANALYZE buffered_jobs');

        $superqueuer = $adapter_factory->getSuperqueuer();

        $job_generator = function () {
            $accum = [249998, 249999];

            for ($i = 0; $i < 100000; $i++) {
                if ($i % 2 == 1) {
                    --$accum[1];
                    if ($accum[1] % 5 === 0) {
                        continue;
                    }
                    yield $accum[1];
                    continue;
                }

                ++$accum[0];
                if ($accum[0] % 5 === 0) {
                    continue;
                }
                yield $accum[0];
            }
        };

        $this->getAsserter()->assertJobsToRun($superqueuer, $uniqid, $job_generator());
    }

    /**
     * @return PostgresProvisioner
     */
    protected function generateProvisioner()
    {
        return new PostgresProvisioner();
    }
}
