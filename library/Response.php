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
class Response
{
    protected $results = array();

    public function addResult($command, $status, $message)
    {
        $this->results[] = array('command' => $command, 'status' => $status, 'message' => $message);
        return $this;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function hasFailures()
    {
        foreach ($this->results as $result) {
            if ($result['status'] === false) {
                return true;
            }
        }
        return false;
    }
}
