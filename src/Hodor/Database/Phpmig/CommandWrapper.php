<?php

namespace Hodor\Database\Phpmig;

use Phpmig\Api\PhpmigApplication;
use Phpmig\Console\Command\StatusCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandWrapper
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(Container $container, OutputInterface $output)
    {
        $this->container = $container;
        $this->output = $output;
    }

    /**
     * @param Application $app
     * @throws ExceptionInterface
     */
    public function showStatus(Application $app)
    {
        putenv('HODOR_CONFIG=' . $this->container->getConfigPath());
        $status_command = new StatusCommand();
        $status_command->setApplication($app);
        $status_command->run(new ArrayInput([]), $this->output);
    }

    public function runMigrations()
    {
        $phpmig = new PhpmigApplication($this->container, $this->output);
        $phpmig_adapter = $this->container->getPhpmigAdapter();
        if (!$phpmig_adapter->hasSchema()) {
            $phpmig_adapter->createSchema();
        }
        $phpmig->up();
    }

    public function rollbackMigrations()
    {
        $phpmig = new PhpmigApplication($this->container, $this->output);
        $phpmig_adapter = $this->container->getPhpmigAdapter();
        if (!$phpmig_adapter->hasSchema()) {
            $phpmig_adapter->createSchema();
        }
        $phpmig->down(0);
    }
}
