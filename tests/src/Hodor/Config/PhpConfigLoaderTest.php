<?php

namespace Hodor\Config;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\Config\PhpConfigLoader
 */
class PhpConfigLoaderTest extends TestCase
{
    private $loader;

    public function setUp()
    {
        parent::setUp();

        $this->loader = new PhpConfigLoader();
    }

    /**
     * @covers ::loadFromFile
     * @expectedException \Exception
     */
    public function testLoadFromNonExistentFile()
    {
        $this->loader->loadFromFile(__DIR__ . '/PhpConfigDoesNotExist.php');
    }

    /**
     * @covers ::loadFromFile
     * @expectedException \Exception
     */
    public function testLoadFromInvalidFile()
    {
        $this->loader->loadFromFile(__DIR__ . '/PhpConfigIsInvalid.php');
    }

    /**
     * @covers ::loadFromFile
     */
    public function testLoadFromFile()
    {
        $this->assertInstanceOf(
            '\Hodor\JobQueue\Config',
            $this->loader->loadFromFile(__DIR__ . '/../../../../config/config.test.php')
        );
    }
}
