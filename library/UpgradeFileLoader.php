<?php

/**
 * DbSmart2 Upgrade File Loader
 *
 * PHP version 5.3
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @author     27 Cubes <info@27cubes.net>
 * @since      1.0.0
 */

namespace Cubes\DbSmart2;

/**
 * Upgrade File Loader
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @since      1.0.0
 */
class UpgradeFileLoader
{
    /**
     * Loads the given file
     *
     * @param  string $filepath
     * @return QueryList
     * @throws \RuntimeException
     */
    public function loadFile($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException('File "' . $filepath . '" does not exist');
        }
        $contents = explode("\n", trim(file_get_contents($filepath)));
        $queries = $this->parseFileContents($contents);
        return new QueryList($queries);
    }

    /**
     * Loads the given list of files
     *
     * @param  array $filepaths
     * @return QueryList
     * @throws \RuntimeException
     */
    public function loadFiles($filepaths = array())
    {
        $list = new QueryList();
        foreach ($filepaths as $filepath) {
            $individualList = $this->loadFile($filepath);
            $list->joinWith($individualList);
        }
        return $list;
    }

    /**
     * Parses the file contents into a [XX][YY] = queryblock matrix
     *
     * @param  array $contents
     * @return array the parsed queries
     */
    protected function parseFileContents($contents = array())
    {
        $queries = array();
        $curXxKey = $curYyKey = null;
        $inQueryBlock = false;
        $contents = array_filter(array_map('trim', $contents));
        foreach ($contents as $line) {
            $matches = array();
            if (!$inQueryBlock && preg_match('/^-- ?\{\{\{ ([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)$/', $line, $matches)) {
                $inQueryBlock = true;
                $curXxKey = $matches[1];
                $curYyKey = $matches[2];
            } elseif ($inQueryBlock && preg_match('/^-- ?\}\}\}$/', $line)) {
                $inQueryBlock = false;
                $curXxKey = $curYyKey = null;
            } elseif ($inQueryBlock) {
                if (!isset($queries[$curXxKey])) {
                    $queries[$curXxKey] = array();
                }
                if (!isset($queries[$curXxKey][$curYyKey])) {
                    $queries[$curXxKey][$curYyKey] = array();
                }
                $queries[$curXxKey][$curYyKey][] = $line;
            }
        }
        return $this->normalizeQueryBlocks($queries);
    }

    /**
     * Normalizes the query blocks from arrays to new-line-delimited strings
     *
     * Queries come into this method in the [XX][YY] = array(...) format and
     * are returned int he [XX][YY] = string format
     *
     * @param  array $queries
     * @return array the normalized queries
     */
    protected function normalizeQueryBlocks($queries)
    {
        foreach ($queries as $xx => $grouped) {
            foreach ($grouped as $yy => $lines) {
                $queries[$xx][$yy] = join("\n", $lines);
            }
        }
        return $queries;
    }
}
