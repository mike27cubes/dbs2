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

class QueryList
{
    protected $queries;

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
     * Joins the given query list into
     *
     * @param  QueryList $toJoin
     * @return self fluent interface
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
}
