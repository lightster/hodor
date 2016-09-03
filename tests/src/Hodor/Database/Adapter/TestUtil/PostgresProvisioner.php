<?php

namespace Hodor\Database\Adapter\TestUtil;

use Exception;
use Hodor\Database\Adapter\Postgres\Factory;
use Hodor\Database\PgsqlAdapter;
use Hodor\Database\Phpmig\CommandWrapper;
use Hodor\Database\Phpmig\Container;
use Symfony\Component\Console\Output\NullOutput;

class PostgresProvisioner extends AbstractProvisioner
{
    public function setUp()
    {
        $phpmig_container = new Container();
        $phpmig_container->addDefaultServices('no-config-file');
        $phpmig_container['hodor.database'] = $this->getAdapterFactory()->getYoPdo();

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
     * @return Factory
     * @throws Exception
     */
    public function generateAdapterFactory()
    {
        $config_path = __DIR__ . '/../../../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return new Factory($config['test']['db']['yo-pdo-pgsql']);
    }
}
