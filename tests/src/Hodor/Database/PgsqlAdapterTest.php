<?php

namespace Hodor\Database;

use Exception;

use PHPUnit_Framework_TestCase;

class PgsqlAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $config_path = __DIR__ . '/../../../config/config.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $this->config = require $config_path;
    }

    /**
     * @expectedException \Exception
     */
    public function testRequestingAConnectionWithoutADsnThrowsAnException()
    {
        $db = new PgsqlAdapter([]);
        $db->getConnection();
    }

    /**
     * @expectedException \Exception
     */
    public function testAConnectionFailureThrowsAnException()
    {
        $db = new PgsqlAdapter(['dsn' => 'host=localhost user=nonexistent']);
        $db->getConnection();
    }

    public function testAConnectionCanBeMade()
    {
        $db = new PgsqlAdapter($this->config['test']['db']['pgsql']);
        $this->assertEquals('resource', gettype($db->getConnection()));
    }
}
