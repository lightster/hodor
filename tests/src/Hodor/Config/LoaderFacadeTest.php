<?php

namespace Hodor\Config;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\Config\LoaderFacade
 */
class LoaderFacadeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::loadFromFile
     * @covers ::setLoaderFactory
     * @covers ::<private>
     */
    public function testFacadeCallsLoaderFactoryLoadFromFile()
    {
        $file_path = __DIR__ . '/PhpConfig.php';

        $loader_factory = $this->getMockBuilder('\Hodor\Config\LoaderFactory')
            ->setMethods(['loadFromFile'])
            ->getMock();
        $loader_factory->expects($this->once())
            ->method('loadFromFile')
            ->with(
                $file_path
            );

        LoaderFacade::setLoaderFactory($loader_factory);
        LoaderFacade::loadFromFile($file_path);
    }

    /**
     * @covers ::loadFromFile
     * @covers ::setLoaderFactory
     * @covers ::<private>
     */
    public function testFacadeReturnsConfig()
    {
        $file_path = __DIR__ . '/../../../../config/config.test.php';

        LoaderFacade::setLoaderFactory(null);
        $this->assertInstanceOf(
            '\Hodor\JobQueue\Config',
            LoaderFacade::loadFromFile($file_path)
        );
    }
}
