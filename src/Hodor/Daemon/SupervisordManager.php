<?php

namespace Hodor\Daemon;

use Exception;
use Hodor\JobQueue\Config;

class SupervisordManager implements ManagerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function setupDaemon()
    {
        $raw_daemon_config = $this->getRawDaemonConfig();
        $config_path = $raw_daemon_config['config_path'];
        $config_contents = '';

        foreach ($this->getDaemonConfig() as $program) {
            $config_contents .= $this->generateProgramText($program) . "\n";
        }

        if (!file_put_contents($config_path, $config_contents)) {
            throw new Exception("Could not write to config file '{$config_path}'.\n");
        }
    }

    /**
     * @return array
     */
    public function getDaemonConfig()
    {
        $programs = [];
        $programs += $this->generateQueuePrograms(
            $this->config->getBufferQueueNames(),
            function ($queue_name) {
                return $this->config->getBufferQueueConfig($queue_name);
            }
        );
        $programs += $this->generateQueuePrograms(
            $this->config->getWorkerQueueNames(),
            function ($queue_name) {
                return $this->config->getWorkerQueueConfig($queue_name);
            }
        );

        return $programs;
    }

    /**
     * @param  array    $queue_names
     * @param  callable $queue_config_generator
     * @return array
     */
    private function generateQueuePrograms(
        array $queue_names,
        callable $queue_config_generator
    ) {
        $raw_daemon_config = $this->getRawDaemonConfig();
        $programs = [];
        foreach ($queue_names as $queue_name) {
            $program_config = array_replace_recursive(
                $raw_daemon_config,
                $queue_config_generator($queue_name)
            );

            $this->evaluateProgramName($program_config);
            $program_config['command'] = $this->generateCommandString($program_config);

            $programs[$program_config['program_name']] = $this->getProgram($program_config);
        }

        return $programs;
    }

    /**
     * @param  array  $program_config
     * @return string
     */
    private function generateCommandString(array $program_config)
    {
        if ('bufferer' === $program_config['queue_type']) {
            $bin_file = 'buffer-worker.php';
        } else {
            $bin_file = 'job-worker.php';
        }

        $program_config['program_prefix'] = '';
        $command_pieces = [
            '/usr/bin/env php',
            escapeshellarg(__DIR__ . '/../../../bin/' . $bin_file),
            '-c ' . escapeshellarg($this->config->getConfigPath()),
            '-q ' . escapeshellarg($program_config['key_name']),
        ];

        return implode(" ", $command_pieces);
    }

    /**
     * @param  array & $program_config
     * @return void
     */
    private function evaluateProgramName(array & $program_config)
    {
        $search = [
            '{{PROGRAM_PREFIX}}',
            '{{QUEUE_TYPE}}',
            '{{QUEUE_NAME}}',
        ];
        $replace = [
            $program_config['program_prefix'],
            $program_config['queue_type'],
            $program_config['key_name'],
        ];

        $program_config['program_name'] = str_replace(
            $search,
            $replace,
            $program_config['program_name']
        );
    }

    /**
     * @return array
     */
    private function getRawDaemonConfig()
    {
        $defaults = [
            'config_path'    => '/etc/supervisord/conf.d/hodor.conf',
            'process_owner'  => 'apache',
            'program_prefix' => 'hodor',
            'program_name' => '{{PROGRAM_PREFIX}}-{{QUEUE_TYPE}}-{{QUEUE_NAME}}',
            'logs'           => [
                'error' => [
                    'path'         => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                    'max_size'     => '10M',
                    'rotate_count' => 2,
                ],
                'debug' => [
                    'path'         => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                    'max_size'     => '10M',
                    'rotate_count' => 2,
                ],
            ],
        ];

        return array_replace_recursive(
            $defaults,
            $this->config->getDaemonConfig('daemon', [])
        );
    }

    /**
     * @param  array $queue_program_config
     * @return array
     */
    private function getProgram(array $queue_program_config)
    {
        return [
            'program_name'            => $queue_program_config['program_name'],
            'command'                 => $queue_program_config['command'],
            'process_name'            => '%(program_name)s_%(process_num)d',
            'numprocs'                => $queue_program_config['process_count'],
            'numprocs_start'          => 0,
            'autorestart'             => 'true',
            'autostart'               => 'true',
            'startsecs'               => 0,
            'startretries'            => 3,
            'user'                    => $queue_program_config['process_owner'],
            'stopsignal'              => 'TERM',
            'stderr_logfile'          => $queue_program_config['logs']['error']['path'],
            'stderr_logfile_maxbytes' => $queue_program_config['logs']['error']['max_size'],
            'stderr_logfile_backups'  => $queue_program_config['logs']['error']['rotate_count'],
            'stdout_logfile'          => $queue_program_config['logs']['debug']['path'],
            'stdout_logfile_maxbytes' => $queue_program_config['logs']['debug']['max_size'],
            'stdout_logfile_backups'  => $queue_program_config['logs']['debug']['rotate_count'],
        ];
    }

    /**
     * @param  array $program
     * @return string
     */
    private function generateProgramText(array $program)
    {
        $text = "[program:{$program['program_name']}]\n";

        unset($program['program_name']);

        foreach ($program as $key => $value) {
            $text .= "{$key}={$value}\n";
        }

        return $text;
    }
}
