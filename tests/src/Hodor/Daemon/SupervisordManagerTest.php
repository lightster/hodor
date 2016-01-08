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

    public function testSetupDaemonGeneratesSupervisordConfig()
    {
        $hodor_base_path = dirname(dirname(dirname(dirname(__DIR__))));

        $supervisord_config_path = __DIR__ . '/../../../../tests/tmp/supervisord.' . uniqid() . '.conf';
        $manager = $this->getSupervisordManager($supervisord_config_path);

        $config_dir = dirname($supervisord_config_path);
        if (!is_dir($config_dir) && !mkdir($config_dir)) {
            throw new Exception("Could not create directory '{$config_dir}'.");
        }

        $manager->setupDaemon();

        $expected_supervisord_config = str_replace(
            '{{HODOR_BASE_PATH}}',
            $hodor_base_path,
            file_get_contents(__DIR__ . '/ExpectedSupervisordConfig.conf')
        );

        $this->assertEquals(
            $expected_supervisord_config,
            file_get_contents($supervisord_config_path)
        );
    }

    /**
     * @param string $supervisord_config_path
     * @return SupervisordManager
     */
    private function getSupervisordManager($supervisord_config_path = null)
    {
        $config_array = require __DIR__ . '/../../../../config/config.test.php';

        $daemon_test_config = $config_array['test']['daemon']['supervisord'];
        $daemon_test_config['daemon']['config_path'] = $supervisord_config_path;

        $config = new Config(
            dirname(__FILE__) . '/ExpectedSupervisordConfig.php',
            $daemon_test_config
        );

        return new SupervisordManager($config);
    }
}
