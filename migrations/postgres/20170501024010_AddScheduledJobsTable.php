<?php

use Hodor\Database\Phpmig\Migration;
use Lstr\YoPdo\YoPdo;

class AddScheduledJobsTable extends Migration
{
    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalUp(YoPdo $yo_pdo)
    {
        $sql = <<<SQL
CREATE TABLE scheduled_jobs
(
    buffered_job_id INT NOT NULL DEFAULT nextval('buffered_jobs_buffered_job_id_seq'::regclass),
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    mutex_id VARCHAR NOT NULL DEFAULT 'hodor:' || currval('buffered_jobs_buffered_job_id_seq'::regclass),
    scheduled_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    scheduled_from VARCHAR NOT NULL
);

ALTER TABLE scheduled_jobs ADD CONSTRAINT scheduled_jobs_pkey PRIMARY KEY (buffered_job_id);
CREATE INDEX scheduled_jobs_run_after ON scheduled_jobs (run_after DESC);

ALTER TABLE buffered_jobs
    ADD COLUMN scheduled_at TIMESTAMP WITH TIME ZONE,
    ADD COLUMN scheduled_from VARCHAR;
ALTER TABLE queued_jobs
    ADD COLUMN scheduled_at TIMESTAMP WITH TIME ZONE,
    ADD COLUMN scheduled_from VARCHAR;
ALTER TABLE successful_jobs
    ADD COLUMN scheduled_at TIMESTAMP WITH TIME ZONE,
    ADD COLUMN scheduled_from VARCHAR;
ALTER TABLE failed_jobs
    ADD COLUMN scheduled_at TIMESTAMP WITH TIME ZONE,
    ADD COLUMN scheduled_from VARCHAR;

UPDATE buffered_jobs SET scheduled_at = inserted_at, scheduled_from = inserted_from;
UPDATE queued_jobs SET scheduled_at = inserted_at, scheduled_from = inserted_from;
UPDATE successful_jobs SET scheduled_at = inserted_at, scheduled_from = inserted_from;
UPDATE failed_jobs SET scheduled_at = inserted_at, scheduled_from = inserted_from;

ALTER TABLE buffered_jobs
    ALTER COLUMN scheduled_at SET DEFAULT NOW(),
    ALTER COLUMN scheduled_at SET NOT NULL,
    ALTER COLUMN scheduled_from SET DEFAULT 'n/a',
    ALTER COLUMN scheduled_from SET NOT NULL;
ALTER TABLE queued_jobs
    ALTER COLUMN scheduled_at SET DEFAULT NOW(),
    ALTER COLUMN scheduled_at SET NOT NULL,
    ALTER COLUMN scheduled_from SET DEFAULT 'n/a',
    ALTER COLUMN scheduled_from SET NOT NULL;
ALTER TABLE successful_jobs
    ALTER COLUMN scheduled_at SET DEFAULT NOW(),
    ALTER COLUMN scheduled_at SET NOT NULL,
    ALTER COLUMN scheduled_from SET DEFAULT 'n/a',
    ALTER COLUMN scheduled_from SET NOT NULL;
ALTER TABLE failed_jobs
    ALTER COLUMN scheduled_at SET DEFAULT NOW(),
    ALTER COLUMN scheduled_at SET NOT NULL,
    ALTER COLUMN scheduled_from SET DEFAULT 'n/a',
    ALTER COLUMN scheduled_from SET NOT NULL;
SQL;

        $yo_pdo->queryMultiple($sql);
    }

    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalDown(YoPdo $yo_pdo)
    {
        $sql = <<<SQL
ALTER TABLE buffered_jobs
    DROP COLUMN scheduled_at,
    DROP COLUMN scheduled_from;
ALTER TABLE queued_jobs
    DROP COLUMN scheduled_at,
    DROP COLUMN scheduled_from;
ALTER TABLE successful_jobs
    DROP COLUMN scheduled_at,
    DROP COLUMN scheduled_from;
ALTER TABLE failed_jobs
    DROP COLUMN scheduled_at,
    DROP COLUMN scheduled_from;

DROP TABLE scheduled_jobs;
SQL;

        $yo_pdo->queryMultiple($sql);
    }
}
