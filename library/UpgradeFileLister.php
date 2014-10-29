<?php

/**
 * DbSmart2 Upgrade File Lister
 *
 * PHP version 5.3
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @author     27 Cubes <info@27cubes.net>
 * @since      1.0.0
 */

namespace Cubes\DbSmart2;

/**
 * Upgrade File Lister
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @since      1.0.0
 */
class UpgradeFileLister
{
    /**
     * Gets the Upgrade File List
     *
     * @param  \Cubes\DbSmart2\Config $config
     * @return array
     */
    public function getFileList(Config $config)
    {
        $files = array_unique(glob($config->getUpgradePath() . '/*.sql'));
        $files = array_filter($files, array($this, 'isFileRelevant'));
        usort($files, array($this, 'upgradeFileSortComparison'));
        return $files;
    }

    /**
     * Determines if the given file is relevant
     *
     * @param  string $file
     * @return bool
     */
    public function isFileRelevant($file)
    {
        $name = trim(substr($file, strrpos($file, '/')), '/');
        if ($name == 'branch-upgrade.sql' || preg_match('/upgrade-([0-9]+)_([0-9]+)_([0-9]+).sql/', $name)) {
            return true;
        }
        return false;
    }

    /**
     * Compares the upgrade files in order to sort the upgrade file list
     *
     * @param  string $a
     * @param  string $b
     * @return int -1,0,1
     */
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
