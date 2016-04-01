<?php

namespace Hodor\MessageQueue\Adapter;

interface MessageInterface
{
    /**
     * @return mixed
     */
    public function getContent();

    public function acknowledge();
}
