<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Configuration handling service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config extends AbstractService
{
    /** @var string Default domain */
    const DEFAULT_DOMAIN = 'general';

    /**
     * Config file location
     *
     * @var string
     */
    protected $configLocation = '';

    /**
     * Custom file location
     *
     * @var string
     */
    protected $customLocation = '';

    /**
     * Container for config data:
     *
     * - Global general config
     * - Domain config
     *
     * @var array
     */
    protected $configs = array(
    );

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (!empty($this->options['root_path'])) {
            $this->configLocation = $this->options['root_path'];
        } else {
            $this->configLocation = Pi::path('config');
        }
        if (!empty($this->options['custom_path'])) {
            $this->customLocation = $this->options['custom_path'];
        } else {
            $this->customLocation = Pi::path('custom');
        }
    }

    /**
     * Get module config(s)
     *
     * @param string $name
     * @param string $module
     *
     * @return mixed
     */
    public function module($name = '', $module = '')
    {
        $moduleConfig = Pi::service('module')->config($name, $module);

        return $moduleConfig;
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

        if (isset($this->configs[$domain])
            && isset($this->configs[$domain][$name])
        ) {
            $value = $this->configs[$domain][$name];
        }

        return $value;
    }

    /**
     * Set a config
     *
     * @param string $name       Name of the config element
     * @param mixed  $value
     * @param string $domain     Configuration domain
     *
     * @return $this
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
     * @return $this
     */
    public function setConfigs($configs, $domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        if (isset($this->configs[$domain])) {
            $this->configs[$domain] = array_merge(
                $this->configs[$domain],
                $configs
            );
        } else {
            $this->configs[$domain] = $configs;
        }

        return $this;
    }

    /**
     * Unset configuration data of a domain
     *
     * @param string    $domain     Configuration domain
     * @return $this
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
     * @return $this
     */
    public function loadDomain($domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        // Load data from cache
        $this->setConfigs(
            (array) Pi::registry('config')->read('system', $domain),
            $domain
        );

        return $this;
    }

    /**
     * Load configuration data from custom or config directory
     *
     * @param string $configFile
     *      Name for the config file located inside var/config and sub folders
     * @param bool   $checkCustom
     *
     * @return array
     */
    public function load($configFile, $checkCustom = true)
    {
        if ('.php' != substr($configFile, -4)) {
            $configFile .= '.php';
        }
        $configs = array();
        $file = '';
        if ($checkCustom) {
            $file = $this->customLocation . '/' . $configFile;
            if (!file_exists($file)) {
                $file = '';
            }
        }
        if (!$file) {
            $file = $this->configLocation . '/' . $configFile;
            if (!file_exists($file)) {
                $file = '';
            }
        }
        if ($file) {
            $configs = include $file;
        }

        return $configs;
    }

    /**
     * Write config data into config file
     *
     * @param string    $file
     * @param array     $data
     * @param bool      $toCustom
     *
     * @return bool
     */
    public function write($file, array $data, $toCustom = false)
    {
        if ('.php' != substr($file, -4)) {
            $file .= '.php';
        }
        $path = $toCustom ? $this->customLocation : $this->configLocation;
        $file = $path . '/' . $file;

        $content = '<?php' . PHP_EOL
            . 'return ' . var_export($data, true) . ';';
        $result = (bool) file_put_contents($file, $content);

        return $result;
    }
}
