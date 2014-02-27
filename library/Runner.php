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

    public function run()
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
}
