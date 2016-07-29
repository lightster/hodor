<?php

namespace Hodor\Daemon;

use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit_Framework_TestCase;
use Hodor\JobQueue\Config;

/**
 * @coversDefaultClass Hodor\Daemon\SupervisordManager
 */
class SupervisordManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getDaemonConfig
     * @covers ::generateQueuePrograms
     * @covers ::evaluateProgramName
     */
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
     * @covers ::__construct
     * @covers ::getDaemonConfig
     * @covers ::generateQueuePrograms
     * @covers ::getProgram
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

    /**
     * @covers ::__construct
     * @covers ::getDaemonConfig
     * @covers ::generateQueuePrograms
     * @covers ::getBinFilePath
     * @covers ::evaluateProgramName
     * @covers ::generateCommandString
     * @covers ::getProgram
     */
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
     * @covers ::__construct
     * @covers ::setupDaemon
     * @covers ::getRawDaemonConfig
     * @covers ::generateProgramText
     * @expectedException \Exception
     */
    public function testSetupDaemonThrowsAnExceptionIfConfigFileIsNotWritable()
    {
        $supervisord_conf = __DIR__ . '/../../../../tests/non-existent/supervisord.' . uniqid() . '.conf';
        $manager = $this->getSupervisordManager($supervisord_conf);

        $manager->setupDaemon();
    }

    /**
     * @covers ::__construct
     * @covers ::setupDaemon
     * @covers ::getRawDaemonConfig
     * @covers ::generateProgramText
     */
    public function testSetupDaemonGeneratesSupervisordConfig()
    {
        $hodor_base_path = dirname(dirname(dirname(dirname(__DIR__))));

        vfsStreamWrapper::register();
        $file_system = new vfsStreamDirectory('supervisor-configs');

        $supervisord_conf = $file_system->url() . '/supervisord.' . uniqid() . '.conf';
        $manager = $this->getSupervisordManager($supervisord_conf);

        $config_dir = dirname($supervisord_conf);
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
            file_get_contents($supervisord_conf)
        );

        unlink($supervisord_conf);
    }

    /**
     * @param string $supervisord_conf
     * @return SupervisordManager
     */
    private function getSupervisordManager($supervisord_conf = null)
    {
        $config_array = require __DIR__ . '/../../../../config/config.test.php';

        $daemon_test_config = $config_array['test']['daemon']['supervisord'];
        $daemon_test_config['daemon']['config_path'] = $supervisord_conf;

        $config = new Config(
            dirname(__FILE__) . '/ExpectedSupervisordConfig.php',
            $daemon_test_config
        );

        return new SupervisordManager($config);
    }
}
