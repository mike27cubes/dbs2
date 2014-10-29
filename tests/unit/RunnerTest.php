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
 * @since      1.0.0
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
}
