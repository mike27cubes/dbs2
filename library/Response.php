<?php

/**
 * DbSmart2 Response
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
 * Response
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @since      %NEXT_VERSION%
 */
class Response
{
    /**
     * Results
     *
     * @var array
     */
    protected $results = array();

    /**
     * Adds a result to the response
     *
     * @param  string $command
     * @param  bool   $status  Whether the result represents a successful action
     * @param  string $message
     * @return self   Fluent interface
     */
    public function addResult($command, $status, $message)
    {
        $this->results[] = array('command' => $command, 'status' => $status, 'message' => $message);
        return $this;
    }

    /**
     * Gets the results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Determines if the response contains failures
     *
     * @return bool
     */
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
