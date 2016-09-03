<?php

namespace Hodor\Database;

use Hodor\Database\Adapter\Postgres\Factory;

class PgsqlAdapter extends ConverterAdapter
{
    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct(new Factory($this, $config));
    }
}
