<?php

namespace Hodor\Database\Phpmig;

use Lstr\YoPdo\YoPdo;
use Phpmig\Migration\Migration as PhpmigMigration;

abstract class Migration extends PhpmigMigration
{
    /**
     * @return void
     */
    public function up()
    {
        /**
         * @var $container Container
         */
        $container = $this->getContainer();
        $yo_pdo = $container->getYoPdo();

        $yo_pdo->transaction()->begin('phpmig');
        $this->transactionalUp($yo_pdo);
        $yo_pdo->transaction()->accept('phpmig');
    }

    /**
     * @return void
     */
    public function down()
    {
        $container = $this->getContainer();
        $yo_pdo = $container['hodor.database'];

        $yo_pdo->transaction()->begin('phpmig');
        $this->transactionalDown($yo_pdo);
        $yo_pdo->transaction()->accept('phpmig');
    }

    /**
     * @param YoPdo $yo_pdo
     */
    abstract protected function transactionalUp(YoPdo $yo_pdo);

    /**
     * @param YoPdo $yo_pdo
     */
    abstract protected function transactionalDown(YoPdo $yo_pdo);
}
