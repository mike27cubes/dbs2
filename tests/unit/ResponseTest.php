<?php

/**
 * Tests the DbSmart2 Response class
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

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = new \Cubes\DbSmart2\Response();
        $this->assertInstanceOf('\Cubes\DbSmart2\Response', $obj);
    }

    public function testAddResultGetResults()
    {
        $input = array(
            array('command' => uniqid('command'), 'status' => rand(0, 1) ? true : false, 'message' => uniqid('message')),
            array('command' => uniqid('command'), 'status' => rand(0, 1) ? true : false, 'message' => uniqid('message')),
            array('command' => uniqid('command'), 'status' => rand(0, 1) ? true : false, 'message' => uniqid('message')),
            array('command' => uniqid('command'), 'status' => rand(0, 1) ? true : false, 'message' => uniqid('message')),
            array('command' => uniqid('command'), 'status' => rand(0, 1) ? true : false, 'message' => uniqid('message')),
            array('command' => uniqid('command'), 'status' => rand(0, 1) ? true : false, 'message' => uniqid('message')),
            array('command' => uniqid('command'), 'status' => false, 'message' => uniqid('message'))
        );
        $obj = new \Cubes\DbSmart2\Response();
        $this->assertFalse($obj->hasFailures());
        $this->assertEquals(array(), $obj->getResults());
        foreach ($input as $row) {
            $retVal = $obj->addResult($row['command'], $row['status'], $row['message']);
            $this->assertInstanceOf('\Cubes\DbSmart2\Response', $retVal);
            $this->assertEquals($obj, $retVal);
        }
        $this->assertEquals($input, $obj->getResults());
        $this->assertTrue($obj->hasFailures());
    }
}
