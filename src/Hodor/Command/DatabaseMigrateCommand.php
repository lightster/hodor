<?php

namespace Hodor\Command;

use Hodor\Database\Phpmig\CommandWrapper;
use Hodor\Database\Phpmig\Container;
use Symfony\Component\Console\Command\Command;
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
        $container = new Container();
        $container->addDefaultServices($input->getArgument('hodor-config'));

        $command_wrapper = new CommandWrapper($container, $output);

        if ($input->getOption('status')) {
            $command_wrapper->showStatus($this->getApplication());
            return;
        }

        $command_wrapper->runMigrations();
    }
}
