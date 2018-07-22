<?php

namespace Hodor\Daemon;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\Daemon\ManagerFactory
 */
class ManagerFactoryTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getManager
     * @expectedException \Exception
     */
    public function testUnknownDaemonTypeThrowsAnException()
    {
        $this->getManagerFactory('unk')->getManager();
    }

    /**
     * @covers ::__construct
     * @covers ::getManager
     */
    public function testSupervisordDaemonTypeReturnsSupervisordManager()
    {
        $this->assertInstanceOf(
            '\Hodor\Daemon\SupervisordManager',
            $this->getManagerFactory('supervisord')->getManager()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getManager
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
