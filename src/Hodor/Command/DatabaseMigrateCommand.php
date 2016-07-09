<?php

namespace Hodor\Command;

use Hodor\Database\Phpmig\Container;
use Hodor\Database\Phpmig\PgsqlPhpmigAdapter;
use Phpmig\Api\PhpmigApplication;
use Phpmig\Console\Command\StatusCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseMigrateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('database:migrate')
            ->setDescription('Run and view the status of database migrations')
            ->addArgument(
                'hodor-config',
                InputArgument::REQUIRED,
                'What is the path to your hodor config?'
            )
            ->addOption(
                'status',
                false,
                InputOption::VALUE_NONE,
                'View the list of migrations and whether they have been ran'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phpmig_container = new Container();
        $phpmig_container->addDefaultServices($input->getArgument('hodor-config'));

        $phpmig = new PhpmigApplication($phpmig_container, $output);
        if ($input->getOption('status')) {
            putenv('HODOR_CONFIG=' . $input->getArgument('hodor-config'));
            $status_command = new StatusCommand();
            $status_command->setApplication($this->getApplication());
            $status_command->run(new ArrayInput([]), $output);
            return;
        }

        $phpmig_adapter = $phpmig_container->getPhpmigAdapter();
        if (!$phpmig_adapter->hasSchema()) {
            $phpmig_adapter->createSchema();
        }
        $phpmig->up();
    }
}
