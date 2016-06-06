<?php

namespace Hodor\Config;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\Config\LoaderFactory
 */
class LoaderFactoryTest extends PHPUnit_Framework_TestCase
{
    private $loader_factory;

    public function setUp()
    {
        parent::setUp();

        $this->loader_factory = new LoaderFactory();
    }

    /**
     * @covers ::getLoaderFromExtension
     * @expectedException \Exception
     */
    public function testRequestingLoaderForUnknownExtensionThrowsAnException()
    {
        $this->loader_factory->getLoaderFromExtension('unk');
    }

    /**
     * @covers ::getLoaderFromExtension
     */
    public function testLoaderForPhpExtensionIsAPhpConfigLoader()
    {
        $this->assertInstanceOf(
            '\Hodor\Config\PhpConfigLoader',
            $this->loader_factory->getLoaderFromExtension('php')
        );
    }

    /**
     * @covers ::loadFromFile
     * @expectedException \Exception
     */
    public function testLoadingWithUnknownExtensionThrowsAnException()
    {
        $this->loader_factory->loadFromFile(__DIR__ . '/Config.unk');
    }

    /**
     * @covers ::loadFromFile
     */
    public function testItIsPossibleToLoadFromAPhpConfigFile()
    {
        $this->assertInstanceOf(
            '\Hodor\JobQueue\Config',
            $this->loader_factory->loadFromFile(__DIR__ . '/../../../../config/config.test.php')
        );
    }
}
