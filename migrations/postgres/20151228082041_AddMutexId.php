<?php

use Hodor\Database\AdapterInterface as DbAdapterInterface;
use Hodor\Database\Phpmig\Migration;

class AddMutexId extends Migration
{
    /**
     * @param DbAdapterInterface $db
     * @return void
     */
    protected function transactionalUp(DbAdapterInterface $db)
    {
        $sql = <<<SQL
ALTER TABLE buffered_jobs
    ADD COLUMN mutex_id VARCHAR DEFAULT 'hodor:' || currval('buffered_jobs_buffered_job_id_seq'::regclass);
ALTER TABLE queued_jobs
    ADD COLUMN mutex_id VARCHAR;
ALTER TABLE successful_jobs
    ADD COLUMN mutex_id VARCHAR;
ALTER TABLE failed_jobs
    ADD COLUMN mutex_id VARCHAR;

UPDATE buffered_jobs
SET mutex_id = 'hodor:' || buffered_job_id;

UPDATE queued_jobs
SET mutex_id = 'hodor:' || buffered_job_id;

UPDATE successful_jobs
SET mutex_id = 'hodor:' || buffered_job_id;

UPDATE failed_jobs
SET mutex_id = 'hodor:' || buffered_job_id;

ALTER TABLE buffered_jobs
    ALTER COLUMN mutex_id SET NOT NULL;
ALTER TABLE queued_jobs
    ALTER COLUMN mutex_id SET NOT NULL;
ALTER TABLE successful_jobs
    ALTER COLUMN mutex_id SET NOT NULL;
ALTER TABLE failed_jobs
    ALTER COLUMN mutex_id SET NOT NULL;
SQL;

        $db->queryMultiple($sql);
    }

    /**
     * @param DbAdapterInterface $db
     * @return void
     */
    protected function transactionalDown(DbAdapterInterface $db)
    {
        $sql = <<<SQL
ALTER TABLE buffered_jobs
    DROP COLUMN mutex_id;
ALTER TABLE queued_jobs
    DROP COLUMN mutex_id;
ALTER TABLE successful_jobs
    DROP COLUMN mutex_id;
ALTER TABLE failed_jobs
    DROP COLUMN mutex_id;
SQL;

        $db->queryMultiple($sql);
    }
}
