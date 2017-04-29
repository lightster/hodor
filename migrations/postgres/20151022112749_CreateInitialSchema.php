<?php

use Hodor\Database\Phpmig\Migration;
use Lstr\YoPdo\YoPdo;

class CreateInitialSchema extends Migration
{
    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalUp(YoPdo $yo_pdo)
    {
        $sql = <<<SQL
CREATE SEQUENCE buffered_jobs_buffered_job_id_seq;
CREATE SEQUENCE failed_jobs_failed_job_id_seq;
CREATE SEQUENCE queued_jobs_queued_job_id_seq;
CREATE SEQUENCE successful_jobs_successful_job_id_seq;

CREATE TABLE buffered_jobs
(
    buffered_job_id INT NOT NULL DEFAULT nextval('buffered_jobs_buffered_job_id_seq'::regclass),
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL
);

CREATE TABLE queued_jobs
(
    queued_job_id INT NOT NULL DEFAULT nextval('queued_jobs_queued_job_id_seq'::regclass),
    buffered_job_id INT NOT NULL UNIQUE,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL,
    superqueued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    superqueued_from VARCHAR NOT NULL
);

CREATE TABLE successful_jobs
(
    successful_job_id INT NOT NULL DEFAULT nextval('successful_jobs_successful_job_id_seq'::regclass),
    buffered_job_id INT NOT NULL UNIQUE,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL,
    superqueued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    superqueued_from VARCHAR NOT NULL,
    started_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    finished_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    ran_from VARCHAR NOT NULL,
    dequeued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    dequeued_from VARCHAR NOT NULL
);

CREATE TABLE failed_jobs
(
    failed_job_id INT NOT NULL DEFAULT nextval('failed_jobs_failed_job_id_seq'::regclass),
    buffered_job_id INT NOT NULL UNIQUE,
    queue_name VARCHAR NOT NULL,
    job_name VARCHAR NOT NULL,
    job_params JSON NOT NULL,
    job_rank INT NOT NULL DEFAULT 5,
    run_after TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    buffered_from VARCHAR NOT NULL,
    inserted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    inserted_from VARCHAR NOT NULL,
    superqueued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    superqueued_from VARCHAR NOT NULL,
    started_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    finished_running_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    ran_from VARCHAR NOT NULL,
    dequeued_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    dequeued_from VARCHAR NOT NULL
);

ALTER TABLE buffered_jobs ADD CONSTRAINT buffered_jobs_pkey PRIMARY KEY (buffered_job_id);
ALTER TABLE queued_jobs ADD CONSTRAINT queued_jobs_pkey PRIMARY KEY (queued_job_id);
ALTER TABLE successful_jobs ADD CONSTRAINT successful_jobs_pkey PRIMARY KEY (successful_job_id);
ALTER TABLE failed_jobs ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (failed_job_id);
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
DROP TABLE buffered_jobs;
DROP TABLE queued_jobs;
DROP TABLE successful_jobs;
DROP TABLE failed_jobs;

DROP SEQUENCE IF EXISTS buffered_jobs_buffered_job_id_seq;
DROP SEQUENCE IF EXISTS failed_jobs_failed_job_id_seq;
DROP SEQUENCE IF EXISTS queued_jobs_queued_job_id_seq;
DROP SEQUENCE IF EXISTS successful_jobs_successful_job_id_seq;
SQL;

        $yo_pdo->queryMultiple($sql);
    }
}
