<?php

namespace Hodor\Database\Driver;

use Exception;

use PHPUnit_Framework_TestCase;

class PgsqlDriverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $config_path = __DIR__ . '/../../../../../config/config.test.php';
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
        $db = new PgsqlDriver([]);
        $db->getConnection();
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testAConnectionFailureThrowsAnException()
    {
        $db = new PgsqlDriver(['dsn' => 'host=localhost user=nonexistent']);
        $db->getConnection();
    }

    public function testAConnectionCanBeMade()
    {
        $db = new PgsqlDriver($this->config['test']['db']['pgsql']);
        $this->assertEquals('resource', gettype($db->getConnection()));
    }
}
