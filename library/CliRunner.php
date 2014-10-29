<?php

/**
 * DbSmart2 CLI Runner
 *
 * PHP version 5.3
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @author     27 Cubes <info@27cubes.net>
 * @since      %NEXT_VERSION%
 */

namespace Cubes\DbSmart2;

/**
 * CLI Runner
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @since      %NEXT_VERSION%
 */
class CliRunner
{
    /**
     * Options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Verbosity level
     *
     * @var int
     */
    protected $verbosity = self::VERBOSITY_NORMAL;

    const VERBOSITY_QUIET = 0;
    const VERBOSITY_NORMAL = 1;
    const VERBOSITY_LOUD = 2;

    /**
     * Constructor
     *
     * @param  array $options
     */
    public function __construct($options = array())
    {
        $this->options = $options;
        $this->setupVerbosity();
    }

    /**
     * Setup verbosity level based on options
     *
     * @return CliRunner fluent interface
     */
    protected function setupVerbosity()
    {
        $this->verbosity = self::VERBOSITY_NORMAL; // default verbosity level
        if (!empty($this->options['V'])) {
            $this->verbosity = self::VERBOSITY_LOUD;
        }
        if (!empty($this->options['q'])) {
            $this->verbosity = self::VERBOSITY_QUIET;
        }
        return $this;
    }

    /**
     * Determines if the verbosity level is "loud"
     *
     * @return bool
     */
    protected function loudOutput()
    {
        return $this->verbosity == self::VERBOSITY_LOUD;
    }

    /**
     * Determines if the verbosity level is "normal" or greater
     *
     * @return bool
     */
    protected function normalOutput()
    {
        return in_array($this->verbosity, array(self::VERBOSITY_NORMAL, self::VERBOSITY_LOUD));
    }

    /**
     * Run
     *
     * @param  string $projectRoot
     * @param  string $command
     * @param  array  $commandOptions
     */
    public function run($projectRoot, $command, $commandOptions = array())
    {
        if (!empty($this->options['h'])) {
            return $this->runHelp();
        }
        $command = strtolower($command);
        $runner = new Runner();
        if (!$runner->validateCommand($command)) {
            return $this->runHelp('Invalid Command "' . $command . '"');
        }
        // Load Config
        $configPath = $projectRoot . '/dbsmart2.json';
        $config = new Config();
        $config->loadFromJsonFile($configPath);
        $response = $runner->loadConfig($config)
                           ->run($command, $commandOptions);
        if ($this->loudOutput()) {
            foreach ($response->getResults() as $result) {
                echo 'Command: ' . $result['command'] . "\n";
                echo 'Status: ' . $result['status'] . "\n";
                echo 'Message: ' . $result['message'] . "\n\n";
            }
        } elseif ($this->normalOutput()) {
            foreach ($response->getResults() as $result) {
                echo $result['message'] . "\n";
            }
        } else {
            // Assumed quiet
            $failures = array();
            if ($response->hasFailures()) {
                foreach ($response->getResults() as $result) {
                    if ($result['status'] === false) {
                        $failures[] = $result['command'] . ': ' . $result['message'];
                    }
                }
                $stdErr = fopen('php://stderr', 'a');
                fwrite($stdErr, "\n" . join("\n", $failures) . "\n");
                fclose($stdErr);
            }
        }
    }

    /**
     * Runs the Help Command
     *
     * @param  string $error
     */
    public function runHelp($error = '')
    {
        echo <<<'EOH'
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
        if (!empty($error) && is_scalar($error)) {
            trigger_error('DBSMART2 ERROR' . "\n" . $error, E_USER_ERROR);
        }
    }
}
