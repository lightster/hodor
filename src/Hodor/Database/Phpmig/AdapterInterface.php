<?php

namespace Hodor\Database\Phpmig;

use Phpmig\Adapter\AdapterInterface as PhpmigAdapterInterface;

interface AdapterInterface extends PhpmigAdapterInterface
{
    /**
     * @return string
     */
    public function getMigrationsPath();
}
