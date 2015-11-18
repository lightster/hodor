<?php

namespace Hodor\Daemon;

use Exception;
use Hodor\JobQueue\Config;

class ManagerFactory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $manager_factories = [
        'supervisord' => '\Hodor\Daemon\SupervisordManager',
    ];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return ManagerInterface
     * @throws Exception
     */
    public function getManager()
    {
        $daemon_config = $this->config->getDaemonConfig();
        $type = $daemon_config['type'];

        if (!isset($this->manager_factories[$type])) {
            throw new Exception(
                "A daemon manager factory is not associated with '{$type}'."
            );
        }

        $manager_factory = $this->manager_factories[$type];

        return new $manager_factory($this->config);
    }
}
