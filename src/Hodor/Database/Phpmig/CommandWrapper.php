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
     * @var string
     */
    private $config_path;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct($config_path, OutputInterface $output)
    {
        $this->config_path = $config_path;
        $this->output = $output;
    }

    /**
     * @param Application $app
     * @throws ExceptionInterface
     */
    public function showStatus(Application $app)
    {
        putenv('HODOR_CONFIG=' . $this->config_path);
        $status_command = new StatusCommand();
        $status_command->setApplication($app);
        $status_command->run(new ArrayInput([]), $this->output);
    }

    public function up()
    {
        $container = new Container();
        $container->addDefaultServices($this->config_path);

        $phpmig = new PhpmigApplication($container, $this->output);
        $phpmig_adapter = $container->getPhpmigAdapter();
        if (!$phpmig_adapter->hasSchema()) {
            $phpmig_adapter->createSchema();
        }
        $phpmig->up();
    }
}
