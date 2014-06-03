<?php

/**
 * DbSmart2 Runner
 *
 * PHP version 5.3
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @author     27 Cubes <info@27cubes.net>
 * @since      %NEXT_VERSION%
 */

namespace Cubes\DbSmart2;

class Runner
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \PDO
     */
    protected $db;

    const COMMAND_REVISIONCHECK = 'revisioncheck';
    const COMMAND_UPGRADE = 'upgrade';
    const COMMAND_DOWNGRADE = 'downgrade';
    const COMMAND_CONNECTIONTEST = 'connectiontest';
    const COMMAND_TABLETEST = 'tabletest';
    const COMMAND_SETUPTRACKER = 'setuptracker';
    const COMMAND_DUMPLOG = 'dumplog';
    const COMMAND_NULL = 'null';

    const TRACKER_TABLE = 'DbSmart2';

    /**
     * Sets the config
     *
     * @param  Config $config
     * @return Runner fluent interface
     */
    public function loadConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Gets the query list
     *
     * @return QueryList
     * @throws \RuntimeException
     */
    public function getQueryList()
    {
        if (!($this->config instanceof Config)) {
            throw new \RuntimeException('Config not loaded');
        }
        $lister = new UpgradeFileLister();
        $loader = new UpgradeFileLoader();
        return $loader->loadFiles($lister->getFileList($this->config));
    }

    /**
     * Gets a list of the "new" schema ids
     *
     * @return array
     */
    public function getNewSchemaIds()
    {
        $queries = $this->getQueryList();
        $queries->filterOutQueries($this->getExecutedSchemaIds());
        return $queries->getSchemaIds();
    }

    /**
     * Runs the given command
     *
     * @param  string $command the command to run
     * @param  array  $options
     * @return Response
     */
    public function run($command, $options = array())
    {
        $runMethod = 'run' . ucfirst($command);
        $response = new Response();
        return call_user_func(array($this, $runMethod), $options, $response);
    }

    /**
     * Run Revision Check
     *
     * @param  array    $options
     * @param  Response $response
     * @return Response
     */
    protected function runRevisioncheck($options = array(), Response $response)
    {
        $resposne = $this->runConnectiontest($options, $response);
        $resposne = $this->runTabletest($options, $response);
        if ($response->hasFailures()) {
            return $response;
        }
        $new = $this->getNewSchemaIds();
        if (count($new) > 0) {
            $response->addResult(self::COMMAND_REVISIONCHECK, false, 'Revisions to run' . "\n\n" . join("\n", $new) . "\n");
        } else {
            $response->addResult(self::COMMAND_REVISIONCHECK, true, 'Up To Date');
        }
        return $response;
    }

    /**
     * Run Downgrade
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    protected function runDowngrade($options = array(), Response $response)
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    /**
     * Run Connection Test
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    protected function runConnectiontest($options = array(), Response $response)
    {
        try {
            $db = $this->getDb();
            $response->addResult(self::COMMAND_CONNECTIONTEST, true, 'Connection Test Passed');
        } catch (\Exception $e) {
            $response->addResult(self::COMMAND_CONNECTIONTEST, false, $e->getMessage());
        }
        return $response;
    }

    /**
     * Run Table Test
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    protected function runTabletest($options = array(), Response $response)
    {
        $db = $this->getDb();
        $sql = 'SHOW CREATE TABLE ' . self::TRACKER_TABLE;
        $exists = false;
        foreach ($db->query($sql) as $row) {
            if (!empty($row)) {
                $exists = true;
                return 'Tracker Table exists';
            }
        }
        if ($exists) {
            $response->addResult(self::COMMAND_TABLETEST, true, 'Tracker Table exists');
        } else {
            $response->addResult(self::COMMAND_TABLETEST, false, 'Tracker Table does not exist');
        }
        return $response;
    }

    /**
     * Run Setup Tracker
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    protected function runSetuptracker($options = array(), Response $response)
    {
        $db = $this->getDb();
        $sql = 'CREATE TABLE ' . self::TRACKER_TABLE . ' (
            schema_id VARCHAR ( 16 ) NOT NULL ,
            schema_md5 VARCHAR ( 32 ) NOT NULL ,
            schema_date DATE NOT NULL ,
            schema_time TIME NOT NULL ,
            PRIMARY KEY ( schema_id ) ,
            INDEX ( schema_md5 )
        ) ENGINE=InnoDb DEFAULT CHARSET=UTF8';
        if ($db->exec($sql)) {
            $response->addResult(self::COMMAND_SETUPTRACKER, true, 'Tracker Table Created');
        } else {
            $response->addResult(self::COMMAND_SETUPTRACKER, false, 'Tracker Table creation failed');
        }
        return $response;
    }

    /**
     * Run Dump Log
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    protected function runDumplog($options = array(), Response $response)
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    /**
     * Run Null Command
     *
     * For Testing Purposes only
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    protected function runNull($options = array(), Response $response)
    {
        return $response->addResult(self::COMMAND_NULL, true, trim('NULL ' . json_encode($options)));
    }

    /**
     * Run Upgrade
     *
     * @param  array $options
     * @param  Response $response
     * @return Response
     */
    public function runUpgrade($options = array(), Response $response)
    {
        $queries = $this->getQueryList();
        $queries->filterOutQueries($this->getExecutedSchemaIds());
        $db = $this->getDb();
        foreach ($queries->getQueries() as $querySetId => $querySet) {
            foreach ($querySet as $queryId => $queryBlock) {
                // TODO: Start transaction
                $splitQueries = $this->splitUpQueryBlock($queryBlock);
                foreach ($splitQueries as $query) {
                    $stmt = $db->prepare($query);
                    if ($stmt === false) {
                        // TODO: ROLLBACK
                        return $response->addResult(self::COMMAND_UPGRADE, false, 'Could not prepare query: ' . join(' - ', $db->errorInfo()));
                    }
                    if (!$stmt->execute()) {
                        // TODO: ROLLBACK
                        return $response->addResult(self::COMMAND_UPGRADE, false, 'Could not execute query: ' . join(' - ', $stmt->errorInfo()));
                    }
                }
                if (!$this->logExecutedSchemaId($querySetId, $queryId, $queryBlock)) {
                    // TODO: ROLLBACK
                    return $response->addResult(self::COMMAND_UPGRADE, false, 'Could not update tracker table');
                }
                // TODO: Commit
            }
        }
        return $response->addResult(self::COMMAND_UPGRADE, true, 'Successfully executed queries' . "\n" . join("\n", $queries->getSchemaIds()));
    }

    /**
     * Logs the given query-set-id and query-id to the tracker table
     *
     * @param  string $querySetId
     * @param  string $queryId
     * @param  string $queryBlock
     * @return bool
     */
    protected function logExecutedSchemaId($querySetId, $queryId, $queryBlock)
    {
        $sql = 'INSERT INTO DbSmart2 (schema_id, schema_md5) VALUES(?, ?)';
        $db = $this->getDb();
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        return $stmt->execute(array($querySetId . '.' . $queryId, md5(trim($queryBlock))));
    }

    /**
     * Splits up the given query block into a list of executable queries
     *
     * @param  string $queryBlock
     * @return array
     */
    protected function splitUpQueryBlock($queryBlock)
    {
        $queries = explode("\n", trim($queryBlock));
        foreach ($queries as $k => $v) {
            $queries[$k] = rtrim($v);
        }
        $queryBlock = join("\n", $queries);
        return explode("\n;", $queryBlock);
    }

    /**
     * Gets a list of the executed schema ids
     *
     * @return array
     */
    protected function getExecutedSchemaIds()
    {
        $db = $this->getDb();
        $stmt = $db->prepare('SELECT schema_id FROM DbSmart2 ORDER BY schema_id ASC');
        if ($stmt === false) {
            throw new \RuntimeException('Could not poll executed schema ids: ' . join(' - ', $db->errorInfo()));
        }
        if (!$stmt->execute()) {
            throw new \RuntimeException('Could not poll executed schema ids: ' . join(' - ', $stmt->errorInfo()));
        }
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Gets the Datbase Connection
     *
     * @return \PDO
     * @throws \RuntimeException, \PDOException
     */
    public function getDb()
    {
        if ($this->db instanceof \PDO) {
            return $this->db;
        }
        if (!($this->config instanceof Config)) {
            throw new \RuntimeException('Config not loaded');
        }
        $db = new \PDO($this->config->getDsn(), $this->config->getUsername(), $this->config->getPassword(), array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        $this->db = $db;
        return $this->db;
    }

    /**
     * Validates the given command
     *
     * @param  string $toTest
     * @return bool
     */
    public function validateCommand($toTest)
    {
        return in_array($toTest, self::getCommandList());
    }

    /**
     * Gets a list ov valid commands
     *
     * @return array
     */
    public static function getCommandList()
    {
        return array(
            self::COMMAND_REVISIONCHECK,
            self::COMMAND_UPGRADE,
            self::COMMAND_DOWNGRADE,
            self::COMMAND_CONNECTIONTEST,
            self::COMMAND_TABLETEST,
            self::COMMAND_SETUPTRACKER,
            self::COMMAND_DUMPLOG,
            self::COMMAND_NULL
        );
    }
}
