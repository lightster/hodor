<?php

namespace Hodor\Config;

use PHPUnit_Framework_TestCase;

class LoaderFactoryTest extends PHPUnit_Framework_TestCase
{
    private $loader_factory;

    public function setUp()
    {
        parent::setUp();

        $this->loader_factory = new LoaderFactory();
    }

    /**
     * @expectedException \Exception
     */
    public function testRequestingLoaderForUnknownExtensionThrowsAnException()
    {
        $this->loader_factory->getLoaderFromExtension('unk');
    }

    public function testLoaderForPhpExtensionIsAPhpConfigLoader()
    {
        $this->assertInstanceOf(
            '\Hodor\Config\PhpConfigLoader',
            $this->loader_factory->getLoaderFromExtension('php')
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testLoadingWithUnknownExtensionThrowsAnException()
    {
        $this->loader_factory->loadFromFile(__DIR__ . '/Config.unk');
    }

    public function testItIsPossibleToLoadFromAPhpConfigFile()
    {
        $this->assertInstanceOf(
            '\Hodor\Config',
            $this->loader_factory->loadFromFile(__DIR__ . '/PhpConfig.php')
        );
    }
}
