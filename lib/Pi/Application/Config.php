<?php
/**
 * Pi config handler
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application;
use Pi;

class Config
{
    const DEFAULT_DOMAIN = 'general';

    /**
     * Config file location
     * @var string
     */
    protected $fileLocation = '';

    /**
     * Container for config data
     * @var array
     */
    protected $configs = array(
        // Global general config
        //'general'  => null,
        // Domain config ...
    );

    /**
     * Constructor
     *
     * @param  string $fileLocation
     */
    public function __construct($fileLocation)
    {
        $this->fileLocation = $fileLocation;
    }

    /**
     * Get a config by name and its domain
     *
     * @param string    $name       Name of the config element
     * @param string    $domain     Configuration domain
     * @return mixed    configuration value
     */
    public function get($name, $domain = null)
    {
        $value = null;
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;

        if (!isset($this->configs[$domain])) {
            $this->loadDomain($domain);
        }

        if (isset($this->configs[$domain]) && isset($this->configs[$domain][$name])) {
            $value = $this->configs[$domain][$name];
        }

        return $value;
    }

    /**
     * Set a config
     *
     * @param string    $name       Name of the config element
     * @param string    $domain     Configuration domain
     * @return Config
     */
    public function set($name, $value, $domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        $this->configs[$domain][$name] = $value;

        return $this;
    }

    /**
     * Set configuration data
     *
     * @param array     $configs    Associative array of config data
     * @param string    $domain     Configuration domain
     * @return Config
     */
    public function setConfigs($configs, $domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        if (isset($this->configs[$domain])) {
            $this->configs[$domain] = array_merge($this->configs[$domain], $configs);
        } else {
            $this->configs[$domain] = $configs;
        }

        return $this;
    }

    /**
     * Unset configuration data of a domain
     *
     * @param string    $domain     Configuration domain
     * @return Config
     */
    public function unsetDomain($domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        if (isset($this->configs[$domain])) {
            $this->configs[$domain] = null;
        }

        return $this;
    }

    /**
     * Load configuration data of a domain from database
     *
     * @param string    $domain     Configuration domain
     * @return Config
     */
    public function loadDomain($domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        // Load data from cache
        $this->setConfigs((array) Pi::service('registry')->config->read('system', $domain), $domain);

        return $this;
    }

    /**
     * Load configuration data from var/config directory
     *
     * @param string    $configFile Name for the config file located inside var/config and sub folders
     * @return array
     */
    public function load($configFile)
    {
        $configs = array();
        $file = $this->fileLocation . '/' . $configFile;
        if (file_exists($file)) {
            $configs = include $file;
        }

        return $configs;
    }
}
