<?php

namespace Hodor\Database\Exception;

use Exception;

class BufferedJobNotFoundException extends Exception
{
    /**
     * @param string $message
     * @param int $buffered_job_id
     * @param array $meta
     */
    public function __construct($message, $buffered_job_id, array $meta)
    {
        parent::__construct($message);
    }
}
