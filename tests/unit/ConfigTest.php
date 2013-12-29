<?php

/**
 * Tests the DbSmart2 Config class
 *
 * PHP version 5.3
 *
 * @vendor     27Cubes
 * @package    DbSmart2
 * @subpackage DmSmart2Test
 * @author     27 Cubes <info@27cubes.net>
 * @since      %NEXT_VERSION%
 */

namespace 27Cubes\DbSmart2;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Useless test to start out
	 */
	public function testGetInstance()
	{
		$obj = \27Cubes\DbSmart2\Config();
		$this->assertInstanceOf('\27Cubes\DbSmart2\Config', $obj);
	}
}
