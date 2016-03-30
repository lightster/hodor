<?php

namespace Hodor\MessageQueue\Adapter;

interface MessageInterface
{
    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @return string
     */
    public function getContentType();

    public function acknowledge();
}
