<?php

namespace Hodor\Database;

use PHPUnit_Framework_TestCase;

class PgsqlAdapterTest extends PHPUnit_Framework_TestCase
{

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
        $db = new PgsqlAdapter(['dsn' => 'host=localhost user=lightster2']);
        $this->assertEquals('resource', gettype($db->getConnection()));
    }
}
