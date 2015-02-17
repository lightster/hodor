<?php

namespace Hodor\Config;

use PHPUnit_Framework_TestCase;

class PhpConfigLoaderTest extends PHPUnit_Framework_TestCase
{
    private $loader;

    public function setUp()
    {
        parent::setUp();

        $this->loader = new PhpConfigLoader();
    }

    /**
     * @expectedException \Exception
     */
    public function testLoadFromNonExistentFile()
    {
        $this->loader->loadFromFile(__DIR__ . '/PhpConfigDoesNotExist.php');
    }

    /**
     * @expectedException \Exception
     */
    public function testLoadFromInvalidFile()
    {
        $this->loader->loadFromFile(__DIR__ . '/PhpConfigIsInvalid.php');
    }

    public function testLoadFromFile()
    {
        $this->assertInstanceOf(
            '\Hodor\Config',
            $this->loader->loadFromFile(__DIR__ . '/PhpConfig.php')
        );
    }
}
