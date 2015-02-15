<?php

namespace Hodor\Config;

use Exception;

class LoaderFactory
{
    /**
     * @var array
     */
    private $loader_classes = [
        'php' => '\Hodor\Config\PhpConfigLoader',
    ];

    /**
     * @param  string $extension
     * @return \Hodor\Config\LoaderInterface
     */
    public function getLoaderFromExtension($extension)
    {
        if (!isset($this->loader_classes[$extension])) {
            throw new Exception(
                "A config loader is not associated with '{$extension}' extension."
            );
        }

        $loader_class = $this->loader_classes[$extension];

        return new $loader_class();
    }

    /**
     * @param  string $file_path
     * @return \Hodor\Config
     */
    public function loadFromFile($file_path)
    {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $loader = $this->getLoaderFromExtension($extension);

        return $loader->loadFromFile($file_path);
    }
}
