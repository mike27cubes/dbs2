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
        $loader = new UpgradeFileLoader();
        return $loader->loadFiles(glob($this->config->getUpgradePath() . '/*.sql'));
    }

    public function getFileList()
    {
        if (!($this->config instanceof Config)) {
            throw new \RuntimeException('Config not loaded');
        }
        $files = array_unique(glob($this->config->getUpgradePath() . '/*.sql'));
        $files = array_filter($files, array($this, 'excludeJunkFilesFromFileList'));
        usort($files, array($this, 'upgradeFileSortComparison'));
        return $files;
    }

    public function excludeJunkFilesFromFileList($file)
    {
        $name = trim(substr($file, strrpos($file, '/')), '/');
        if ($name == 'branch-upgrade.sql' || preg_match('/upgrade-([0-9]+)_([0-9]+)_([0-9]+).sql/', $name)) {
            return true;
        }
        return false;
    }

    public function upgradeFileSortComparison($a, $b)
    {
        $aName = trim(substr($a, strrpos($a, '/')), '/');
        $bName = trim(substr($b, strrpos($b, '/')), '/');
        if ($aName == 'branch-upgrade.sql') {
            return 1;
        } elseif ($bName == 'branch-upgrade.sql') {
            return -1;
        }
        $aVersion = trim(substr($aName, strrpos($aName, '-')), '-');
        $aVersion = array_pad(explode('_', trim(substr($aVersion, 0, strpos($aVersion, '.')), '.')), -3, 0);
        $bVersion = trim(substr($bName, strrpos($bName, '-')), '-');
        $bVersion = array_pad(explode('_', trim(substr($bVersion, 0, strpos($bVersion, '.')), '.')), -3, 0);
        for ($i = 0; $i < 3; $i++) {
            if ((int) $aVersion[$i] > (int) $bVersion[$i]) {
                return 1;
            } elseif ((int) $aVersion[$i] < (int) $bVersion[$i]) {
                return -1;
            }
        }
        return 0;
    }
}
