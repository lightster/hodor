<?php

namespace Hodor\Command;

use Hodor\Config\LoaderFacade as Config;
use Hodor\Daemon\ManagerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DaemonGenerateConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('daemon:generate-config')
            ->setDescription('Generate the daemon config file')
            ->addArgument(
                'hodor-config',
                InputArgument::REQUIRED,
                'What is the path to your hodor config?'
            )
            ->addOption(
                'json',
                false,
                InputOption::VALUE_NONE,
                'Will cause command to output JSON rather than writing to supervisor config'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::loadFromFile($input->getArgument('hodor-config'));
        $daemonizer = new ManagerFactory($config);
        $manager = $daemonizer->getManager();

        if ($input->getOption('json')) {
            $output->writeln(json_encode($manager->getDaemonConfig()));
        } else {
            $manager->setupDaemon();
        }
    }
}
