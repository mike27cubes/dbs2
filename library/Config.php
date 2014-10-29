<?php

/**
 * DbSmart2 Config
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
 * Config
 *
 * @vendor     27 Cubes
 * @package    DbSmart2
 * @since      %NEXT_VERSION%
 */
class Config
{
    /**
     * DSN
     *
     * @var string
     */
    protected $dsn;

    /**
     * DB Username
     *
     * @var string
     */
    protected $username;

    /**
     * DB Password
     *
     * @var string
     */
    protected $password;

    /**
     * Path to upgrade files
     *
     * @var string
     */
    protected $upgradePath;

    /**
     * Config constructor
     *
     * @param  array $details
     */
    public function __construct($details = array())
    {
        if (is_array($details) && count($details) > 0) {
            $this->loadConfig($details);
        }
    }

    /**
     * Loads the configuration from the given json file
     *
     * @param  string $filePath
     * @return Config fluent interface
     * @throws \InvalidArgumentException
     */
    public function loadFromJsonFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('Config file "' . $filePath . '" does not exist');
        }
        $contents = trim(file_get_contents($filePath));
        return $this->loadConfig(json_decode($this->parseFileReplacements($contents, $filePath), true));
    }

    /**
     * Parses some replacements from the config file
     *
     * @param  string $contents config file contents
     * @param  string $filePath path to config file
     * @return string parsed file contents
     */
    protected function parseFileReplacements($contents, $filePath)
    {
        $replacements = array(
            '__DIR__' => dirname($filePath)
        );
        foreach ($replacements as $search => $replace) {
            $contents = str_replace($search, $replace, $contents);
        }
        return $contents;
    }

    /**
     * Loads the config details
     *
     * @param  array $details
     * @return Config fluent interface
     */
    public function loadConfig($details = array())
    {
        if (!empty($details['customconfig'])) {
            return $this->loadCustomConfig($details['customconfig']);
        }
        $map = array(
            'dsn' => 'dsn',
            'username' => 'username',
            'password' => 'password',
            'upgrade_path' => 'upgradePath'
        );
        foreach ($map as $param => $property) {
            if (isset($details[$param])) {
                $this->$property = $details[$param];
            }
        }
        return $this;
    }

    /**
     * Loads config via a custom script
     *
     * Script must return an array of data readable by Config::loadConfig()
     *
     * @param  string $configscript path to custom configuration script
     * @return Config fluent interface
     */
    protected function loadCustomConfig($configscript)
    {
        return $this->loadConfig(require $configscript);
    }

    /**
     * Gets the configured Database Name
     *
     * @return string
     */
    public function getDbName()
    {
        $matches = array();
        // mysql:dbname=testdb;host=127.0.0.1
        if (!preg_match('/dbname=([a-zA-Z0-9\._-]+);/', $this->getDsn(), $matches)) {
            return '';
        }
        return $matches[1];
    }

    /**
     * Magic Method caller
     *
     * @param  string $name
     * @param  array  $params
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($name, $params = array())
    {
        if (substr($name, 0, 3) == 'get') {
            return $this->getter($name, $params);
        }
        throw new \BadMethodCallException('Invalid method "' . $name . '"');
    }

    /**
     * Magic Method Getter
     *
     * @param  string $name
     * @param  array  $params
     * @return mixed
     * @throws \BadMethodCallException
     */
    protected function getter($name, $params = array())
    {
        $property = lcfirst(substr($name, 3));
        if (!property_exists($this, $property)) {
            throw new \BadMethodCallException('Invalid getter "' . $name . '"');
        }
        return $this->$property;
    }
}
