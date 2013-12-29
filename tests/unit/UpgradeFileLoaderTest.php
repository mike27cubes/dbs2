<?php

/**
 * Tests the DbSmart2 UpgradeFileLoader class
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

class UpgradeFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = \27Cubes\DbSmart2\UpgradeFileLoader();
        $this->assertInstanceOf('\27Cubes\DbSmart2\UpgradeFileLoader', $obj);
    }

    public function testLoadFile()
    {
        $obj = \27Cubes\DbSmart2\UpgradeFileLoader();
        $file = $this->dataDir . '/individual/singlequery.sql';
        $test = $obj->loadFile($file);
        $expected = new \27Cubes\DbSmart2\QueryList(array(
            'indv' => array(
                '001' => 'CREATE TABLE IF NOT EXISTS `DbSmart2_Tests` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;'
            )
        ));
        $this->assertEquals($expected, $test, $file);
    }

    public function testLoadFiles()
    {
        $obj = \27Cubes\DbSmart2\UpgradeFileLoader();
        $files = array(
            $this->dataDir . '/individual/singlequery.sql',
            $this->dataDir . '/individual/manyqueries1.sql'
        );
        $test = $obj->loadFiles($files);
        $expected = new \27Cubes\DbSmart2\QueryList(array(
            'indv' => array(
                '001' => 'CREATE TABLE IF NOT EXISTS `DbSmart2_Tests` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;',
                'abc' => 'ALTER TABLE `DbSmart2_Tests` ADD `flag` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT 0 AFTER `id`, ADD INDEX (`flag`);'
                'abd' => 'ALTER TABLE `DbSmart2_Tests` ADD `flag2` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT 0 AFTER `flag`, ADD INDEX (`flag2`);'
            ),
            'indv2' => array(
                '001' => 'CREATE TABLE IF NOT EXISTS `DbSmart2_Tests2` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;',
                '002' => 'CREATE TABLE IF NOT EXISTS `DbSmart2_Tests3` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;',
                '3' => 'CREATE TABLE IF NOT EXISTS `DbSmart2_Tests4` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;',
            )
        ));
        $this->assertEquals($expected, $test, print_r($files, true));
    }
}
