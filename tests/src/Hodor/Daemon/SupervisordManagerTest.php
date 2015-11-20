<?php

namespace Hodor\Daemon;

use PHPUnit_Framework_TestCase;
use Hodor\JobQueue\Config;

class SupervisordManagerTest extends PHPUnit_Framework_TestCase
{
    public function testDaemonConfigIsGeneratedAsExpected()
    {
        $expected_codebase_path = dirname(dirname(dirname(dirname(__DIR__))));
        $expected_config_path = __FILE__;

        $expected = [
            'hodor-superqueuer-default' => [
                'program_name'            => 'hodor-superqueuer-default',
                'command'                 => "/usr/bin/env php '{$expected_codebase_path}/src/Hodor/Daemon/../../../bin/superqueuer.php' -c '{$expected_config_path}'",
                'process_name'            => '%(program_name)s_%(process_num)d',
                'numprocs'                => 1,
                'numprocs_start'          => 0,
                'autorestart'             => 'true',
                'autostart'               => 'true',
                'startsecs'               => 0,
                'startretries'            => 3,
                'user'                    => 'apache',
                'stopsignal'              => 'TERM',
                'stderr_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                'stderr_logfile_maxbytes' => '10MB',
                'stderr_logfile_backups'  => 2,
                'stdout_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                'stdout_logfile_maxbytes' => '10MB',
                'stdout_logfile_backups'  => 2,
            ],
            'hodor-bufferer-default' => [
                'program_name'            => 'hodor-bufferer-default',
                'command'                 => "/usr/bin/env php '{$expected_codebase_path}/src/Hodor/Daemon/../../../bin/buffer-worker.php' -c '{$expected_config_path}' -q 'default'",
                'process_name'            => '%(program_name)s_%(process_num)d',
                'numprocs'                => 10,
                'numprocs_start'          => 0,
                'autorestart'             => 'true',
                'autostart'               => 'true',
                'startsecs'               => 0,
                'startretries'            => 3,
                'user'                    => 'apache',
                'stopsignal'              => 'TERM',
                'stderr_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                'stderr_logfile_maxbytes' => '10MB',
                'stderr_logfile_backups'  => 2,
                'stdout_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                'stdout_logfile_maxbytes' => '10MB',
                'stdout_logfile_backups' => 2,
            ],
            'hodor-bufferer-special' => [
                'program_name'            => 'hodor-bufferer-special',
                'command'                 => "/usr/bin/env php '{$expected_codebase_path}/src/Hodor/Daemon/../../../bin/buffer-worker.php' -c '{$expected_config_path}' -q 'special'",
                'process_name'            => '%(program_name)s_%(process_num)d',
                'numprocs'                => 10,
                'numprocs_start'          => 0,
                'autorestart'             => 'true',
                'autostart'               => 'true',
                'startsecs'               => 0,
                'startretries'            => 3,
                'user'                    => 'apache',
                'stopsignal'              => 'TERM',
                'stderr_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                'stderr_logfile_maxbytes' => '10MB',
                'stderr_logfile_backups'  => 2,
                'stdout_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                'stdout_logfile_maxbytes' => '10MB',
                'stdout_logfile_backups'  => 2,
            ],
            'hodor-worker-default' => [
                'program_name'            => 'hodor-worker-default',
                'process_name'            => '%(program_name)s_%(process_num)d',
                'command'                 => "/usr/bin/env php '{$expected_codebase_path}/src/Hodor/Daemon/../../../bin/job-worker.php' -c '{$expected_config_path}' -q 'default'",
                'numprocs'                => 10,
                'numprocs_start'          => 0,
                'autorestart'             => 'true',
                'autostart'               => 'true',
                'startsecs'               => 0,
                'startretries'            => 3,
                'user'                    => 'apache',
                'stopsignal'              => 'TERM',
                'stderr_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                'stderr_logfile_maxbytes' => '10MB',
                'stderr_logfile_backups'  => 2,
                'stdout_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                'stdout_logfile_maxbytes' => '10MB',
                'stdout_logfile_backups'  => 2,
            ],
            'hodor-worker-intense' => [
                'program_name'            => 'hodor-worker-intense',
                'command'                 => "/usr/bin/env php '{$expected_codebase_path}/src/Hodor/Daemon/../../../bin/job-worker.php' -c '{$expected_config_path}' -q 'intense'",
                'process_name'            => '%(program_name)s_%(process_num)d',
                'numprocs'                => 2,
                'numprocs_start'          => 0,
                'autorestart'             => 'true',
                'autostart'               => 'true',
                'startsecs'               => 0,
                'startretries'            => 3,
                'user'                    => 'apache',
                'stopsignal'              => 'TERM',
                'stderr_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                'stderr_logfile_maxbytes' => '10MB',
                'stderr_logfile_backups'  => 2,
                'stdout_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                'stdout_logfile_maxbytes' => '10MB',
                'stdout_logfile_backups'  => 2,
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->getSupervisordManager()->getDaemonConfig()
        );
    }

    /**
     * @return SupervisordManager
     */
    private function getSupervisordManager()
    {
        $config_array = require __DIR__ . '/../../../../config/config.test.php';
        $config = new Config(
            __FILE__,
            $config_array['test']['daemon']['supervisord']
        );

        return new SupervisordManager($config);
    }
}
