<?php

namespace Hodor\Config;

class LoaderFacade
{
    /**
     * @var \Hodor\LoaderFactory
     */
    private static $loader_factory;

    /**
     * @param  string $file_path
     * @return \Hodor\Config
     */
    public static function loadFromFile($file_path)
    {
        return self::getLoaderFactory()->loadFromFile($file_path);
    }

    /**
     * @param \Hodor\Config\LoaderFactory $loader_factory
     */
    public static function setLoaderFactory(LoaderFactory $loader_factory = null)
    {
        self::$loader_factory = $loader_factory;
    }

    /**
     * @return \Hodor\Config\LoaderFactory
     */
    private static function getLoaderFactory()
    {
        if (self::$loader_factory) {
            return self::$loader_factory;
        }

        self::$loader_factory = new LoaderFactory();

        return self::$loader_factory;
    }
}
