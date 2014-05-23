<?php

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
    const COMMAND_DUMPLOG = 'dumplog';

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

    public function getNewSchemaIds()
    {
        $queries = $this->getQueryList();
        $queries->filterOutQueries($this->getExecutedSchemaIds());
        return $queries->getSchemaIds();
    }

    public function run($command, $options = array())
    {
        $runMethod = 'run' . ucfirst($command);
        return call_user_func(array($this, $runMethod), $options);
    }

    protected function runRevisioncheck($options = array())
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    protected function runDowngrade($options = array())
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    protected function runConnectiontest($options = array())
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    protected function runTabletest($options = array())
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    protected function runDumplog($options = array())
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented');
    }

    public function runUpgrade($options = array())
    {
        $queries = $this->getQueryList();
        $queries->filterOutQueries($this->getExecutedSchemaIds());
        $db = $this->getDb();
        foreach ($queries->getQueries() as $querySetId => $querySet) {
            foreach ($querySet as $queryId => $queryBlock) {
                // TODO: Start transaction
                $queries = $this->splitUpQueryBlock($queryBlock);
                foreach ($queries as $query) {
                    $stmt = $db->prepare($query);
                    if ($stmt === false) {
                        // TODO: ROLLBACK
                        throw new \RuntimeException('Could not run query: ' . join(' - ', $db->errorInfo()));
                    }
                    if (!$stmt->execute()) {
                        // TODO: ROLLBACK
                        throw new \RuntimeException('Could not run query: ' . join(' - ', $stmt->errorInfo()));
                    }
                }
                if (!$this->logExecutedSchemaId($querySetId, $queryId, $queryBlock)) {
                    // TODO: ROLLBACK
                    throw new \RuntimeException('Could not update schema id log');
                }
                // TODO: Commit
            }
        }
        return true;
    }

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

    protected function splitUpQueryBlock($queryBlock)
    {
        $queries = explode("\n", trim($queryBlock));
        foreach ($queries as $k => $v) {
            $queries[$k] = rtrim($v);
        }
        $queryBlock = join("\n", $queries);
        return explode("\n;", $queryBlock);
    }

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
        try {
            $db = new \PDO($this->config->getDsn(), $this->config->getUsername(), $this->config->getPassword(), array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (\PDOException $e) {
        }
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
            self::COMMAND_DUMPLOG
        );
    }
}
