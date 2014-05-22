<?php

/**
 * DbSmart2 Query List
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
 * Query List
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @since      %NEXT_VERSION%
 */
class QueryList
{
    /**
     * The queries
     *
     * @var array
     */
    protected $queries = array();

    /**
     * Constructor
     *
     * @param  array $queries [optional] the queries
     */
    public function __construct($queries = array())
    {
        if (is_array($queries) && !empty($queries)) {
            // TODO: validate structure of array eg $queries[xx][yy] = string
            $this->queries = $queries;
        }
    }

    /**
     * Gets the queries from this list
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Gets the schema ids
     *
     * @return array
     */
    public function getSchemaIds()
    {
        $ids = array();
        foreach ($this->queries as $xx => $grouped) {
            foreach ($grouped as $yy => $ignored) {
                $ids[] = $xx . '.' . $yy;
            }
        }
        return $ids;
    }

    /**
     * Joins the given query list into
     *
     * @param  QueryList $toJoin
     * @return QueryList fluent interface
     */
    public function joinWith(QueryList $toJoin)
    {
        foreach ($toJoin->getQueries() as $xx => $grouped) {
            foreach ($grouped as $yy => $queryBlock) {
                if (!isset($this->queries[$xx])) {
                    $this->queries[$xx] = array();
                }
                if (!isset($this->queries[$xx][$yy])) {
                    $this->queries[$xx][$yy] = '';
                }
                $this->queries[$xx][$yy] = trim($this->queries[$xx][$yy] . "\n" . $queryBlock);
            }
        }
        return $this;
    }

    /**
     * Filters out queries
     *
     * @param  array $idsToRemove
     * @return QueryList
     */
    public function filterOutQueries($idsToRemove = array())
    {
        // Remove values that don't match the DBSmart Schema Id pattern
        $idsToRemove = array_filter($idsToRemove, function($v) {
            return strpos($v, '.') !== false;
        });
        foreach ($idsToRemove as $schemaId) {
            list($xx, $yy) = explode('.', $schemaId, 2);
            if (isset($this->queries[$xx][$yy])) {
                unset($this->queries[$xx][$yy]);
            }
        }
        foreach (array_keys($this->queries) as $xx) {
            $this->queries[$xx] = array_filter($this->queries[$xx]);
        }
        return $this;
    }
}
