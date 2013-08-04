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
use Exception;
use Memcache as MemcacheExtension;

/**
 * Memcache service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Memcache extends AbstractService
{
    /** @var array Instances */
    protected static $instances = array();

    /** @var array Default options */
    protected $defaultOptions = array(
        'port'              => 11211,
        'persistent'        => true,
        'weight'            => 1,
        'timeout'           => 1,
        'retry_interval'    => 15,
        'status'            => true,
        'failure_callback'  => null
    );

    /**
     * Load options
     *
     * @param string|array $config
     * @return array
     */
    protected function loadOptions($config)
    {
        if (is_string($config)) {
            $config = Pi::config()->load(sprintf('memcache.%s.php', $config));
        }

        if (isset($config['host'])) {
            // Transform it into associative arrays
            $config = array(0 => $config);
        }
        $servers = array();
        foreach ($config as $idx => $server) {
            $servers[] = array_merge($this->defaultOptions, $server);
        }

        return $servers;
    }

    /**
     * Load an instance
     *
     * @param array|null $config
     * @return MemcacheExtension
     * @throws exception
     */
    public function load($config = null)
    {
        if (!extension_loaded('memcache')) {
            throw new exception('Memcache extension is not available!');
        }
        // Load default Memcached handler from Pi::persist to keep consistency
        if (empty($config)) {
            return Pi::persist()->loadHandler('Memcache')->getEngine();
        }

        $configKey = is_array($config) ? serialize($config) : $config;
        if (isset(static::$instances[$configKey])) {
            return static::$instances[$configKey];
        }

        static::$instances[$configKey] = false;
        $options = $this->loadOptions($config);
        if (empty($options)) {
            throw new exception('No valid options!');
        }
        $memcache = new MemcacheExtension;
        foreach ($options as $server) {
            $status = $memcache->addServer(
                $server['host'], $server['port'], $server['persistent'],
                $server['weight'], $server['timeout'],
                $server['retry_interval'],
                $server['status'], $server['failure_callback']
            );
        }
        static::$instances[$configKey] = $memcache;

        return static::$instances[$configKey];
    }
}
