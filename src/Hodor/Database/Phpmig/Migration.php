<?php

namespace Hodor\Database\Phpmig;

use Hodor\Database\AdapterInterface as DbAdapterInterface;
use Phpmig\Migration\Migration as PhpmigMigration;

abstract class Migration extends PhpmigMigration
{
    /**
     * @return void
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['hodor.database'];

        $db->beginTransaction();
        $this->transactionalUp($db);
        $db->commitTransaction();
    }

    /**
     * @return void
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['hodor.database'];

        $db->beginTransaction();
        $this->transactionalDown($db);
        $db->commitTransaction();
    }

    /**
     * @param DbAdapterInterface $db
     */
    abstract protected function transactionalUp(DbAdapterInterface $db);

    /**
     * @param DbAdapterInterface $db
     */
    abstract protected function transactionalDown(DbAdapterInterface $db);
}
