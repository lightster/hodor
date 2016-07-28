<?php

namespace Hodor\Database\Adapter;

interface DequeuerInterface
{
    /**
     * @param array $meta
     */
    public function markJobAsSuccessful(array $meta);

    /**
     * @param array $meta
     */
    public function markJobAsFailed(array $meta);
}
