<?php

use Hodor\Database\Phpmig\Migration;
use Lstr\YoPdo\YoPdo;

class AddInitialIndexes extends Migration
{
    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalUp(YoPdo $yo_pdo)
    {
        $sql = <<<SQL
CREATE INDEX buffered_jobs_superqueuer_filter ON buffered_jobs (mutex_id, job_rank, buffered_at);
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
DROP INDEX IF EXISTS buffered_jobs_superqueuer_filter;
SQL;

        $yo_pdo->queryMultiple($sql);
    }
}
