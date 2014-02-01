<?php

namespace Cubes\DbSmart2;

class Runner
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Sets the config
     *
     * @param  Config $config
     * @return Runner fluent interface
     */
    public function loadConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Gets the query list
     *
     * @return QueryList
     * @throws \RuntimeException
     */
    public function getQueryList()
    {
        if (!($this->config instanceof Config)) {
            throw new \RuntimeException('Config not loaded');
        }
        $lister = new UpgradeFileLister();
        $loader = new UpgradeFileLoader();
        return $loader->loadFiles($lister->getFileList($this->config));
    }
}
