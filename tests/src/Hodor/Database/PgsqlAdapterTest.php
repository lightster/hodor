<?php

namespace Hodor\Database;

use Exception;

use Hodor\Database\Phpmig\CommandWrapper;
use Hodor\Database\Phpmig\Container;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass Hodor\Database\PgsqlAdapter
 */
class PgsqlAdapterTest extends AbstractAdapterTest
{
    public function setUp()
    {
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
     * @covers ::getPhpmigAdapter
     */
    public function testPhpmigAdapterCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\Database\Phpmig\PgsqlPhpmigAdapter',
            $this->getAdapter()->getPhpmigAdapter()
        );
    }

    /**
     * @return PgsqlAdapter
     * @throws Exception
     */
    protected function generateAdapter()
    {
        $config_path = __DIR__ . '/../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return new PgsqlAdapter($config['test']['db']['yo-pdo-pgsql']);
    }
}
