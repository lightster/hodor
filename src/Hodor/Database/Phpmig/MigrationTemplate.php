<?= "<?php\n";?>

use Hodor\Database\AdapterInterface as DbAdapterInterface;
use Hodor\Database\Phpmig\Migration;

class <?= $className ?> extends Migration
{
    /**
     * @param DbAdapterInterface $db
     * @return void
     */
    protected function transactionalUp(DbAdapterInterface $db)
    {
    }

    /**
     * @param DbAdapterInterface $db
     * @return void
     */
    protected function transactionalDown(DbAdapterInterface $db)
    {
    }
}
