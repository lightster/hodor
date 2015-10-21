<?php

namespace Hodor\Daemon;

interface ManagerInterface
{
    /**
     * @return void
     */
    public function setupDaemon();

    /**
     * @return array
     */
    public function getDaemonConfig();
}
