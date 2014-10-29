<?php

/**
 * Tests the DbSmart2 CliRunner class
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

class CliRunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Useless test to start out
     */
    public function testGetInstance()
    {
        $obj = new \Cubes\DbSmart2\CliRunner();
        $this->assertInstanceOf('\Cubes\DbSmart2\CliRunner', $obj);
    }

    public function testVerbosityOptions()
    {
        $tests = array(
            array('options' => array('v' => true, 'V' => true, 'q' => true), 'loud' => false, 'normal' => false),
            array('options' => array('v' => true, 'V' => true, 'q' => false), 'loud' => true, 'normal' => true),
            array('options' => array('v' => true, 'V' => false, 'q' => true), 'loud' => false, 'normal' => false),
            array('options' => array('v' => true, 'V' => false, 'q' => false), 'loud' => false, 'normal' => true),
            array('options' => array('v' => false, 'V' => true, 'q' => true), 'loud' => false, 'normal' => false),
            array('options' => array('v' => false, 'V' => true, 'q' => false), 'loud' => true, 'normal' => true),
            array('options' => array('v' => false, 'V' => false, 'q' => true), 'loud' => false, 'normal' => false),
            array('options' => array('v' => false, 'V' => false, 'q' => false), 'loud' => false, 'normal' => true)
        );
        foreach ($tests as $data) {
            $runner = new \Cubes\DbSmart2\CliRunner($data['options']);
            $reflObj = new \ReflectionObject($runner);
            $loud = $reflObj->getMethod('loudOutput');
            $loud->setAccessible(true);
            $normal = $reflObj->getMethod('normalOutput');
            $normal->setAccessible(true);
            $this->assertEquals($data['loud'], $loud->invoke($runner), print_r($data, true));
            $this->assertEquals($data['normal'], $normal->invoke($runner), print_r($data, true));
        }
    }

    public function testRunHelp()
    {
        $expected = <<<'EOH'
DBSMART2

Usage: project/vendor/bin/dbsmart2 [switches] [command]

Switches
  -h  View Help Message (what you are viewing right now)
  -v  Normal verbosity. Prints all command results and success messages to
      STDOUT, errors to STDERR.
  -V  Loud verbosity. Prints all command results, success messages and debug
      info to STDOUT, errors to STDERR.
  -q  Quiet mode. Nothing is printed to STDOUT, cancels out -v and -V. Errors
      are still printed to STDERR.

Commands
  revisioncheck  - Check to see which revisions need to be executed. This is the
                   default command. Automatically executed by upgrade command.
  upgrade        - Execute all pending revisions.
  setuptracker   - Sets up the DBSMART tracker table.
  connectiontest - Test the configured database connection. Automatically
                   executed by revisioncheck, upgrade, setuptracker, tabletest
                   and dumplog commands.
  tabletest      - Test for the existence of the DBSMART tracker table.
                   Automatically executed by revisioncheck, upgrade,
                   setuptracker, and dumplog commands.
  dumplog        - Dump the contents of the DBSMART tracker table.

Proposed Commands
  downgrade      - Downgrade by executing specific single or sets of rollback
                   revisions

EOH;
        $runner = new \Cubes\DbSmart2\CliRunner();
        $this->expectOutputString($expected);
        $runner->runHelp();
    }


    public function testRun_HelpViaOptions()
    {
        $expected = <<<'EOH'
DBSMART2

Usage: project/vendor/bin/dbsmart2 [switches] [command]

Switches
  -h  View Help Message (what you are viewing right now)
  -v  Normal verbosity. Prints all command results and success messages to
      STDOUT, errors to STDERR.
  -V  Loud verbosity. Prints all command results, success messages and debug
      info to STDOUT, errors to STDERR.
  -q  Quiet mode. Nothing is printed to STDOUT, cancels out -v and -V. Errors
      are still printed to STDERR.

Commands
  revisioncheck  - Check to see which revisions need to be executed. This is the
                   default command. Automatically executed by upgrade command.
  upgrade        - Execute all pending revisions.
  setuptracker   - Sets up the DBSMART tracker table.
  connectiontest - Test the configured database connection. Automatically
                   executed by revisioncheck, upgrade, setuptracker, tabletest
                   and dumplog commands.
  tabletest      - Test for the existence of the DBSMART tracker table.
                   Automatically executed by revisioncheck, upgrade,
                   setuptracker, and dumplog commands.
  dumplog        - Dump the contents of the DBSMART tracker table.

Proposed Commands
  downgrade      - Downgrade by executing specific single or sets of rollback
                   revisions

EOH;
        $runner = new \Cubes\DbSmart2\CliRunner(array('h' => true));
        $this->expectOutputString($expected);
        $runner->run('', '');
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testRun_InvalidCommand()
    {
        $runner = new \Cubes\DbSmart2\CliRunner();
        $this->expectOutputRegex('/.*/');
        $runner->run('', uniqid('Some Invalid Command'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRun_MissingConfigFile()
    {
        $runner = new \Cubes\DbSmart2\CliRunner();
        $runner->run('/dev/null', \Cubes\DbSmart2\Runner::COMMAND_NULL);
    }

    public function testRun_Null()
    {
        $expected = 'NULL []' . "\n";
        $projectRoot = dirname(__DIR__) . '/data';
        $runner = new \Cubes\DbSmart2\CliRunner();
        $this->expectOutputString($expected);
        $runner->run($projectRoot, \Cubes\DbSmart2\Runner::COMMAND_NULL);
    }
}
