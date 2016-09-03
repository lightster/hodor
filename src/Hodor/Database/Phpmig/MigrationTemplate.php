<?= "<?php\n";?>

use Hodor\Database\Phpmig\Migration;
use Lstr\YoPdo\YoPdo;

class <?= $className ?> extends Migration
{
    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalUp(YoPdo $yo_pdo)
    {
    }

    /**
     * @param YoPdo $yo_pdo
     * @return void
     */
    protected function transactionalDown(YoPdo $yo_pdo)
    {
    }
}
