<?php

namespace Hodor\Daemon;

use PHPUnit_Framework_TestCase;

class ManagerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testUnknownDaemonTypeThrowsAnException()
    {
        $this->getManagerFactory('unk')->getManager();
    }

    public function testSupervisordDaemonTypeReturnsSupervisordManager()
    {
        $this->assertInstanceOf(
            '\Hodor\Daemon\SupervisordManager',
            $this->getManagerFactory('supervisord')->getManager()
        );
    }

    /**
     * @param string $daemon_type
     * @return ManagerFactory
     */
    private function getManagerFactory($daemon_type)
    {
        $config = $this->getMockBuilder('\Hodor\JobQueue\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getDaemonConfig'])
            ->getMock();
        $config->expects($this->any())
            ->method('getDaemonConfig')
            ->will($this->returnValue([
                'type' => $daemon_type,
            ]));

        return new ManagerFactory($config);
    }
}
