<?php

namespace Hodor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestGenerateConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test:generate-config')
            ->setDescription('Generate a config file for tests')
            ->addOption(
                'config-file',
                false,
                InputOption::VALUE_REQUIRED,
                'Config file to save the config to',
                __DIR__ . '/../../../config/config.test.php'
            )
            ->addOption(
                'postgres-host',
                false,
                InputOption::VALUE_REQUIRED,
                'Host name to use for postgres',
                'localhost'
            )
            ->addOption(
                'postgres-dbname',
                false,
                InputOption::VALUE_REQUIRED,
                'Database name to use for postgres',
                'test_hodor'
            )
            ->addOption(
                'rabbitmq-host',
                false,
                InputOption::VALUE_REQUIRED,
                'Host name to use for rabbitmq',
                '127.0.0.1'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = [
            'postgres-host'   => $input->getOption('postgres-host'),
            'postgres-dbname' => $input->getOption('postgres-dbname'),
            'rabbitmq-host'   => $input->getOption('rabbitmq-host'),
        ];

        $template = function (array $options) {
            return require __DIR__ . '/../../../config/dist/config.test.php';
        };
        $config_contents = "<?php\nreturn " . var_export($template($options), true) . ";";

        file_put_contents($input->getOption('config-file'), $config_contents);
    }
}
