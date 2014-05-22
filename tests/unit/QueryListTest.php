<?php

/**
 * Tests the DbSmart2 QueryList class
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

class QueryListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = new \Cubes\DbSmart2\QueryList();
        $this->assertInstanceOf('\Cubes\DbSmart2\QueryList', $obj);
    }

    public function testConstructorGetQueries()
    {
        $input = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            )
        );
        $obj = new \Cubes\DbSmart2\QueryList($input);
        $this->assertEquals($input, $obj->getQueries());
    }

    public function testGetSchemaIds()
    {
        $input = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            )
        );
        $expected = array('x1.y1', 'x2.y1', 'x2.y2');
        $obj = new \Cubes\DbSmart2\QueryList($input);
        $this->assertEquals($expected, $obj->getSchemaIds());
    }

    public function testJoinWith()
    {
        $input1 = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            )
        );
        $input2 = array(
            'x1' => array(
                'z1' => 'SELECT CURDATE();'
            ),
            'x3' => array(
                'z1' => 'SELECT CURTIME();',
                'z2' => 'SELECT NOW();'
            )
        );
        $expected = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();',
                'z1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            ),
            'x3' => array(
                'z1' => 'SELECT CURTIME();',
                'z2' => 'SELECT NOW();'
            )
        );
        $obj = new \Cubes\DbSmart2\QueryList($input1);
        $this->assertEquals($expected, $obj->joinWith(new \Cubes\DbSmart2\QueryList($input2))->getQueries());
    }

    public function testFilterOutQueries()
    {
        $input1 = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            )
        );
        $input2 = array(
            'x1' => array(
                'z1' => 'SELECT CURDATE();'
            ),
            'x3' => array(
                'z1' => 'SELECT CURTIME();',
                'z2' => 'SELECT NOW();'
            )
        );
        $filterOutIds = array('x1.y1', 'x2.y2', 'x3.z3');
        $expected = array(
            'x1' => array(
                'z1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();'
            ),
            'x3' => array(
                'z1' => 'SELECT CURTIME();',
                'z2' => 'SELECT NOW();'
            )
        );
        $obj = new \Cubes\DbSmart2\QueryList($input1);
        $obj->joinWith(new \Cubes\DbSmart2\QueryList($input2));
        $this->assertEquals($expected, $obj->filterOutQueries($filterOutIds)->getQueries());
    }

    public function testFilterOutQueries_InvalidSchemaIds()
    {
        $input1 = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            )
        );
        $input2 = array(
            'x1' => array(
                'z1' => 'SELECT CURDATE();'
            ),
            'x3' => array(
                'z1' => 'SELECT CURTIME();',
                'z2' => 'SELECT NOW();'
            )
        );
        $filterOutIds = array('x1y1', 'x2y2', 'x3z3');
        $expected = array(
            'x1' => array(
                'y1' => 'SELECT CURDATE();',
                'z1' => 'SELECT CURDATE();'
            ),
            'x2' => array(
                'y1' => 'SELECT CURTIME();',
                'y2' => 'SELECT NOW();'
            ),
            'x3' => array(
                'z1' => 'SELECT CURTIME();',
                'z2' => 'SELECT NOW();'
            )
        );
        $obj = new \Cubes\DbSmart2\QueryList($input1);
        $obj->joinWith(new \Cubes\DbSmart2\QueryList($input2));
        $this->assertEquals($expected, $obj->filterOutQueries($filterOutIds)->getQueries());
    }
}
