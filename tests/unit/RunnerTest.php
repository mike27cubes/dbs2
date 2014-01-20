<?php

/**
 * Tests the DbSmart2 Runner class
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

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $this->assertInstanceOf('\Cubes\DbSmart2\Runner', $obj);
    }

    public function testLoadConfig()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $ret = $obj->loadConfig(new \Cubes\DbSmart2\Config(array('upgrade_path' => dirname(__DIR__) . '/data/runner')));
        $this->assertInstanceOf('\Cubes\DbSmart2\Runner', $ret);
        $this->assertEquals($obj, $ret);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetQueryList_NoConfigLoaded()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $obj->getQueryList();
    }

    public function testGetQueryList()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $list = $obj->loadConfig(new \Cubes\DbSmart2\Config(array('upgrade_path' => dirname(__DIR__) . '/data/runner')))
                    ->getQueryList();
        $expected = new \Cubes\DbSmart2\QueryList(array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();',
                'y2' => 'SELECT CURTIME();'
            ),
            'x2' => array(
                'y3' => 'SELECT NOW();',
            ),
            'x3' => array(
                'y4' => 'SELECT UNIX_TIMESTAMP();',
            )
        ));
        $this->assertEquals($expected, $list);
    }

    public function testGetFileList()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $path = realpath(dirname(__DIR__) . '/data/runner_filelist_upgradeswithbranchfile');
        $list = $obj->loadConfig(new \Cubes\DbSmart2\Config(array('upgrade_path' => $path)))
                    ->getFileList();
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

    /**
     * @expectedException RuntimeException
     */
    public function testGetFileList_NoConfigLoader()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $obj->getFileList();
    }

    public function testGetFileList_ExcludeJunkFiles()
    {
        $obj = new \Cubes\DbSmart2\Runner();
        $path = realpath(dirname(__DIR__) . '/data/runner_filelist_excludejunk');
        $list = $obj->loadConfig(new \Cubes\DbSmart2\Config(array('upgrade_path' => $path)))
                    ->getFileList();
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
        $obj = new \Cubes\DbSmart2\Runner();
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
