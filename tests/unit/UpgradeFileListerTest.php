<?php

/**
 * Tests the DbSmart2 UpgradeFileLister class
 *
 * PHP version 5.3
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @subpackage DbSmart2Tests
 * @author     27 Cubes <info@27cubes.net>
 * @since      %NEXT_VERSION%
 */

namespace Cubes\DbSmart2Tests;

class UpgradeFileListerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = new \Cubes\DbSmart2\UpgradeFileLister();
        $this->assertInstanceOf('\Cubes\DbSmart2\UpgradeFileLister', $obj);
    }

    public function testGetFileList()
    {
        $obj = new \Cubes\DbSmart2\UpgradeFileLister();
        $path = realpath(dirname(__DIR__) . '/data/runner_filelist_upgradeswithbranchfile');
        $list = $obj->getFileList(new \Cubes\DbSmart2\Config(array('upgrade_path' => $path)));
        $expected = array(
            $path . '/upgrade-0_0_1.sql',
            $path . '/upgrade-0_2_5.sql',
            $path . '/upgrade-0_10_9.sql',
            $path . '/upgrade-1_1_2.sql',
            $path . '/upgrade-2_0_0.sql',
            $path . '/upgrade-14_0_0.sql',
            $path . '/branch-upgrade.sql'
        );
        $this->assertInternalType('array', $list);
        $this->assertEquals($expected, $list);
    }

    public function testGetFileList_ExcludeJunkFiles()
    {
        $obj = new \Cubes\DbSmart2\UpgradeFileLister();
        $path = realpath(dirname(__DIR__) . '/data/runner_filelist_excludejunk');
        $list = $obj->getFileList(new \Cubes\DbSmart2\Config(array('upgrade_path' => $path)));
        $expected = array(
            $path . '/upgrade-0_0_1.sql',
            $path . '/upgrade-0_2_5.sql',
            $path . '/branch-upgrade.sql'
        );
        $this->assertInternalType('array', $list);
        $this->assertEquals($expected, $list);
    }

    public function testUpgradeFileSortComparison()
    {
        $list = array(
            '/upgrade-0_0_1.sql',
            '/upgrade-1_1_2.sql',
            '/upgrade-2_0_0.sql',
            '/upgrade-14_0_0.sql',
            '/upgrade-0_2_5.sql',
            '/upgrade-0_2_5.sql',
            '/upgrade-0_10_9.sql',
            '/branch-upgrade.sql'
        );
        shuffle($list);
        $obj = new \Cubes\DbSmart2\UpgradeFileLister();
        $expected = array(
            '/upgrade-0_0_1.sql',
            '/upgrade-0_2_5.sql',
            '/upgrade-0_2_5.sql',
            '/upgrade-0_10_9.sql',
            '/upgrade-1_1_2.sql',
            '/upgrade-2_0_0.sql',
            '/upgrade-14_0_0.sql',
            '/branch-upgrade.sql'
        );
        usort($list, array($obj, 'upgradeFileSortComparison'));
        $this->assertInternalType('array', $list);
        $this->assertEquals($expected, $list);
    }
}
