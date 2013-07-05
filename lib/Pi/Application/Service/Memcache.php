<?php
/**
 * Memecache service
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
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;
use Pi,
    Exception,
    Memcache as MemcacheExtension;

class Memcache extends AbstractService
{
    protected static $instances = array();
    protected $defaultOptions = array(
        'port'              => 11211,
        'persistent'        => true,
        'weight'            => 1,
        'timeout'           => 1,
        'retry_interval'    => 15,
        'status'            => true,
        'failure_callback'  => null
    );

    protected function loadOptions($config)
    {
        if (is_string($config)) {
            $config = Pi::config()->load(sprintf('memcache.%s.php', $config));
        }

        if (isset($config['host'])) {
            $config = array(0 => $config); // Transform it into associative arrays
        }
        $servers = array();
        foreach ($config as $idx => $server) {
            $servers[] = array_merge($this->defaultOptions, $server);
        }

        return $servers;
    }

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
