<?php

namespace Hodor\Database\Adapter;

interface FactoryInterface
{
    /**
     * @return BufferWorkerInterface
     */
    public function getBufferWorker();

    /**
     * @return SuperqueuerInterface
     */
    public function getSuperqueuer();

    /**
     * @return DequeuerInterface
     */
    public function getDequeuer();
}
