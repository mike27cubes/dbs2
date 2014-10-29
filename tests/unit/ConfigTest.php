<?php

/**
 * Tests the DbSmart2 Config class
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

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $this->assertInstanceOf('\Cubes\DbSmart2\Config', $obj);
    }

    public function testInitialStateGetters()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $this->assertEmpty($obj->getDsn());
        $this->assertEmpty($obj->getDbName());
        $this->assertEmpty($obj->getUsername());
        $this->assertEmpty($obj->getPassword());
        $this->assertEmpty($obj->getUpgradePath());
    }

    public function testConstructorLoadGetters()
    {
        $dbname = uniqid('dbname');
        $input = array(
            'dsn' => uniqid('mysql:dbname=' . $dbname . ';host=127.0.0.1'),
            'username' => uniqid('username-'),
            'password' => uniqid('password-'),
            'upgrade_path' => __DIR__
        );
        $obj = new \Cubes\DbSmart2\Config($input);
        $this->assertEquals($input['dsn'], $obj->getDsn());
        $this->assertEquals($dbname, $obj->getDbName());
        $this->assertEquals($input['username'], $obj->getUsername());
        $this->assertEquals($input['password'], $obj->getPassword());
        $this->assertEquals($input['upgrade_path'], $obj->getUpgradePath());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testCallInvalidMethodName()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $obj->someInvalidMethod();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testGetterInvalidMethodName()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $obj->getInvalidMethod();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadFromJsonFile_FileNotFound()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $obj->loadFromJsonFile(__DIR__ . '/file_not_found.json');
    }

    public function testLoadFromJsonFileGetters()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $filepath = dirname(__DIR__) . '/data/test-config.json';
        $retVal = $obj->loadFromJsonFile($filepath);
        $this->assertEquals($obj, $retVal);
        $expected = json_decode(trim(file_get_contents($filepath)), true);
        $this->assertEquals($expected['dsn'], $obj->getDsn());
        $this->assertEquals($expected['username'], $obj->getUsername());
        $this->assertEquals($expected['password'], $obj->getPassword());
        $this->assertEquals(str_replace('__DIR__', dirname($filepath), $expected['upgrade_path']), $obj->getUpgradePath());
    }

    public function testLoadFromJsonFile_CustomConfig()
    {
        $obj = new \Cubes\DbSmart2\Config();
        $configroot = dirname(__DIR__) . '/data/';
        $configdata = $configroot . 'test-config.json';
        $configfile = $configroot . 'test-config-customloader.json';
        $retVal = $obj->loadFromJsonFile($configfile);
        $this->assertEquals($obj, $retVal);
        $expected = json_decode(trim(file_get_contents($configdata)), true);
        $this->assertEquals($expected['dsn'], $obj->getDsn());
        $this->assertEquals($expected['username'], $obj->getUsername());
        $this->assertEquals($expected['password'], $obj->getPassword());
        // Note: Now replacements done on custom loaded config data
        $this->assertEquals($expected['upgrade_path'], $obj->getUpgradePath());
    }
}
