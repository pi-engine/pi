<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Setup;

/**
 * Configuration handling service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config
{
    /** @var string Root path */
    protected $root = '';

    /**
     * Container for config data:
     *
     * @var array
     */
    protected $container = array();

    public function __construct($root = null)
    {
        $this->root = $root ?: dirname(dirname(__DIR__)) . '/config';
    }

    /**
     * Set configuration data
     *
     * @param string    $name       Configuration name
     * @param array     $configs    Associative array of config data
     *
     * @return $this
     */
    public function setConfigs($name, array $configs)
    {
        $this->container[$name] = $configs;

        return $this;
    }

    /**
     * Set configuration data
     *
     * @param string    $name   Configuration name
     * @param string    $key
     * @param mixed     $value
     * @return $this
     */
    public function set($name, $key, $value)
    {
        $this->container[$name][$key] = $value;

        return $this;
    }

    /**
     * Get configuration data
     *
     * @param string    $name   Configuration name
     * @param string    $key
     *
     * @return mixed
     */
    public function get($name, $key = null)
    {
        $result = null;
        if (isset($this->container[$name])) {
            $result = $this->container[$name];
            if ($key) {
                $result = isset($result[$key]) ? $result[$key] : null;
            }
        }

        return $result;
    }

    /**
     * Load configuration data from custom or config directory
     *
     * @param string    $file
     * @param string    $name
     *
     * @return array
     */
    public function load($file, $name = null)
    {
        $file = $this->root . '/' . $file;
        if (file_exists($file)) {
            $configs = include $file;
            if ($name) {
                $this->container[$name] = $configs;
            } else {
                $this->container = array_merge($this->container, $configs);
            }
        }

        return $this;
    }

    /**
     * Write config data into config file
     *
     * @param string        $file   Full path to file
     * @param array|string  $data
     *
     * @return bool
     */
    public function write($file, $data)
    {
        if (!is_string($data)) {
            $data = '<?php' . PHP_EOL
                . '// Generated on ' . date('Y-m-d H:i:s') . PHP_EOL
                . 'return ' . var_export($data, true) . ';';
        }
        $result = (bool) file_put_contents($file, $data);

        return $result;
    }
}
