<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Configuration handling service
 *
 * Sample code
 *
 * ```
 *  // Get a system config
 *  $value = Pi::config(<name>);
 *
 *  // Get a config of a module
 *  $value = Pi::config(<name>, <module>);
 *
 *  // Get a category of system configs
 *  $values = Pi::config('', '', <categoryOrDomain>);
 *
 *  // Get a category of configs of a module
 *  $values = Pi::config('', <module>, <categoryOrDomain>);
 *
 *  // Get all system configs
 *  $values = Pi::config('');
 *
 *  // Get all configs of a module
 *  $values = Pi::config('', <module>);
 *
 *  // Set a system config
 *  Pi::config()->set(<name>, <value>);
 *
 *  // Set a system config to a category
 *  Pi::config()->set(<name>, <value>, <category>);
 *
 *  // Set a system config to a category
 *  Pi::config()->set(<name>, <value>, <category>);
 *
 *  // Set system configs to a category
 *  Pi::config()->set(<configs>, <category>);
 *  Pi::config()->setDomain(<configs>, <category>);
 *
 *  // Unset system configs from a category
 *  Pi::config()->unsetDomain(<category>);
 *
 *  // Load system configs to a category
 *  Pi::config()->loadDomain(<category>);
 *
 *  // Load configs from a file, checking `var/config/custom/<file>` then `var/config/<file>`
 *  $configs = Pi::config()->load(<file-name>);
 *
 *  // Load configs from a file, only from `var/config/<file>`
 *  $configs = Pi::config()->load(<file-name>, false);
 *
 *  // Write configs to `var/config/<file>`
 *  Pi::config()->load(<[data]>, <file-name>);
 *
 *  // Write configs to `var/config/custom/<file>`
 *  Pi::config()->load(<[data]>, <file-name>, true);
 * ```
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
     * Container for system config data:
     *
     * @var array
     */
    protected $configs = array();

    /**
     * Container for system config-domain map
     *
     * @var array  `<domain> => array(<config>)`
     */
    protected $configsDomain = array();

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
            $this->customLocation =$this->configLocation . '/custom';
        }
    }

    /**
     * Get module config(s)
     *
     * @param string $name
     * @param string $module
     *
     * @return mixed
     * @deprecated
     */
    public function module($name = '', $module = '')
    {
        $module = $module ?: Pi::service('module')->current();
        return $this->get($name, $module);
    }

    /**
     * Get a config by name from a module,
     * or a category of configs if name not specified
     *
     * @param string    $name       Name of the config element
     * @param string    $module     Module
     * @param string    $domain     Domain (category) name
     *
     * @return mixed|array Configuration value(s)
     */
    public function get($name, $module = '', $domain = '')
    {
        $module = $module ?: 'system';
        if ('system' == $module) {
            $value = $this->getSystemConfig($name, $domain);
        } else {
            $configs = Pi::registry('config')->read($module, $domain);
            if ($name) {
                $value = isset($configs[$name]) ? $configs[$name] : null;
            } else {
                $value = $configs;
            }
        }

        return $value;
    }

    /**
     * Get a system config
     *
     * @param string    $name       Name of the config element
     * @param string    $domain     Domain (category) name
     *
     * @return mixed|array    configuration value
     */
    public function getSystemConfig($name, $domain = '')
    {
        if ($domain && !isset($this->configsDomain[$domain])) {
            $this->loadDomain($domain);
        }
        if ($name) {
            $result = isset($this->configs[$name])
                ? $this->configs[$name] : null;
        } else {
            $result = array();
            if ($domain) {
                if (isset($this->configsDomain[$domain])) {
                    foreach ($this->configsDomain[$domain] as $key) {
                        if (isset($this->configs[$key])) {
                            $result[$key] = $this->configs[$key];
                        } else {
                            $result[$key] = null;
                        }
                    }
                }
            } else {
                $result = $this->configs;
            }
        }

        return $result;
    }

    /**
     * Set a config, or a category of configs
     *
     * @param string|array  $name Name of the config element, or associative configs
     * @param mixed|string  $value Config value or config domain if first arg is array
     * @param string $domain     Configuration domain
     *
     * @return $this
     */
    public function set($name, $value = '', $domain = '')
    {
        if (is_scalar($name)) {
            $configs    = array($name => $value);
        } else {
            $configs    = $name;
            $domain     = $value;
        }
        $this->setDomain($configs, $domain);

        return $this;
    }

    /**
     * Set a category of configs
     *
     * @param array $configs Associative configs
     * @param string $domain     Configuration domain
     *
     * @return $this
     * @deprecated
     */
    public function setConfigs($configs, $domain = '')
    {
        return $this->set($configs, $domain);
    }

    /**
     * Set configuration data to a domain (category)
     *
     * @param array     $configs    Associative array of config data
     * @param string    $domain     Configuration domain
     *
     * @return $this
     */
    public function setDomain($configs, $domain = '')
    {
        $this->configs = array_replace($this->configs, $configs);
        if ($domain) {
            if (isset($this->configsDomain[$domain])) {
                $this->configsDomain[$domain] = array_merge(
                    $this->configsDomain[$domain],
                    array_keys($configs)
                );
            } else {
                $this->configsDomain[$domain] = array_keys($configs);
            }
        }

        return $this;
    }

    /**
     * Unset configuration data of a domain
     *
     * @param string    $domain     Configuration domain
     * @return $this
     */
    public function unsetDomain($domain = '')
    {
        if ($domain) {
            if (isset($this->configsDomain[$domain])) {
                foreach ($this->configsDomain[$domain] as $key) {
                    if (isset($this->configs[$key])) {
                        unset($this->configs[$key]);
                    }
                }
                $this->configsDomain[$domain] = array();
            }
        } else {
            $this->configs          = array();
            $this->configsDomain    = array();
        }

        return $this;
    }

    /**
     * Load system configuration data of a domain from database
     *
     * @param string    $domain     Configuration domain
     *
     * @return $this
     */
    public function loadDomain($domain = '')
    {
        // Load data from cache
        $this->setDomain(
            Pi::registry('config')->read('system', $domain),
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
            $file = $this->getPath($configFile, true);
            if (!file_exists($file)) {
                $file = '';
            }
        }
        if (!$file) {
            $file = $this->getPath($configFile);
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
        $file = $this->getPath($file, $toCustom);

        try {
            if (!Pi::service('file')->exists($file)) {
                Pi::service('file')->touch($file);
            } elseif (!is_writable($file)) {
                Pi::service('file')->chmod($file, intval('0777', 8));
            }
        } catch (\Exception $e) {
            trigger_error(
                'Config file `%s` not writable: ' . $e->getMessage(),
                $file
            );

            return false;
        }

        $content = '<?php' . PHP_EOL
            . '// Generated on ' . date('Y-m-d H:i:s') . PHP_EOL
            . 'return ' . var_export($data, true) . ';';
        $result = (bool) file_put_contents($file, $content);

        return $result;
    }

    /**
     * Get full path to a config file
     *
     * @param string $file
     * @param bool $isCustom
     *
     * @return string
     */
    public function getPath($file, $isCustom = false)
    {
        $path = $isCustom ? $this->customLocation : $this->configLocation;
        $file = $path . '/' . $file;

        return $file;
    }
}
