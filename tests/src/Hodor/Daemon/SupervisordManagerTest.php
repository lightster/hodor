<?php

namespace Hodor\Daemon;

use PHPUnit_Framework_TestCase;
use Hodor\JobQueue\Config;

class SupervisordManagerTest extends PHPUnit_Framework_TestCase
{
    public function testDaemonConfigContainsExpectedProcesses()
    {
        $expected = [
            'hodor-superqueuer-default',
            'hodor-bufferer-default',
            'hodor-bufferer-special',
            'hodor-worker-default',
            'hodor-worker-intense',
        ];

        $this->assertEquals(
            $expected,
            array_keys($this->getSupervisordManager()->getDaemonConfig())
        );
    }

    /**
     * @dataProvider provideDaemonProcesses
     */
    public function testProcessesContainExpectedKeys()
    {
        $expected = [
            'program_name',
            'command',
            'process_name',
            'numprocs',
            'numprocs_start',
            'autorestart',
            'autostart',
            'startsecs',
            'startretries',
            'user',
            'stopsignal',
            'stderr_logfile',
            'stderr_logfile_maxbytes',
            'stderr_logfile_backups',
            'stdout_logfile',
            'stdout_logfile_maxbytes',
            'stdout_logfile_backups',
        ];

        foreach ($this->getSupervisordManager()->getDaemonConfig() as $process) {
            $this->assertEquals($expected, array_keys($process));
        }
    }

    public function testDaemonConfigIsGeneratedAsExpected()
    {
        $this->assertEquals(
            require __DIR__ . '/ExpectedSupervisordConfig.php',
            $this->getSupervisordManager()->getDaemonConfig()
        );
    }

    /**
     * @return array
     */
    public function provideDaemonProcesses()
    {
        $rows = [];
        foreach ($this->getSupervisordManager()->getDaemonConfig() as $process) {
            $rows[] = [$process];
        }

        return $rows;
    }

    /**
     * @return SupervisordManager
     */
    private function getSupervisordManager()
    {
        $config_array = require __DIR__ . '/../../../../config/config.test.php';
        $config = new Config(
            dirname(__FILE__) . '/ExpectedSupervisordConfig.php',
            $config_array['test']['daemon']['supervisord']
        );

        return new SupervisordManager($config);
    }
}
