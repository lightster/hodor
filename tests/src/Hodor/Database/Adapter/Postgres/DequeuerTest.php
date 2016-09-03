<?php

namespace Hodor\Database\Adapter\Postgres;

use Exception;
use Hodor\Database\Adapter\DequeuerTest as DequeuerBaseTest;
use Hodor\Database\PgsqlAdapter;
use Hodor\Database\Phpmig\CommandWrapper;
use Hodor\Database\Phpmig\Container;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Postgres\Dequeuer
 */
class DequeuerTest extends DequeuerBaseTest
{
    public function setUp()
    {
        parent::setUp();

        $phpmig_container = new Container();
        $phpmig_container->addDefaultServices('no-config-file');
        $phpmig_container['hodor.database'] = $this->getAdapter();

        $command_wrapper = new CommandWrapper($phpmig_container, new NullOutput());
        $command_wrapper->rollbackMigrations();
        $command_wrapper->runMigrations();
    }

    public function tearDown()
    {
        parent::tearDown();

        // without forcing garbage collection, the DB connections
        // are not guaranteed to be disconnected; force GC
        gc_collect_cycles();
    }

    /**
     * @return PgsqlAdapter
     * @throws Exception
     */
    protected function generateAdapter()
    {
        $config_path = __DIR__ . '/../../../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return new PgsqlAdapter($config['test']['db']['yo-pdo-pgsql']);
    }
}
