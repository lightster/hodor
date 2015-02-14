<?php

namespace Hodor;

use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function testDatabaseConfigCanBeRetrieved($options)
    {
        $config = new Config($options);

        $this->assertEquals($options['database'], $config->getDatabaseConfig());
    }

    public function configProvider()
    {
        return [
            [[
                'database' => [
                    'username' => 'some_username',
                ],
            ]],
        ];
    }
}
