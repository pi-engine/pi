<?php
/**
 * Kernel persist
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
 * @subpackage      Persist
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Persist;

/**
 * Note: this storage does not support namespace or tag
 */
class MemcacheStorage extends AbstractStorage
{
    /**
     * Default Values
     */
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11211;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_WEIGHT  = 1;
    const DEFAULT_TIMEOUT = 1;
    const DEFAULT_RETRY_INTERVAL = 15;
    const DEFAULT_STATUS = true;
    const DEFAULT_FAILURE_CALLBACK = null;

    /**
     * Available options
     *
     * =====> (array) servers :
     * an array of memcached server ; each memcached server is described by an associative array :
     * 'host' => (string) : the name of the memcached server
     * 'port' => (int) : the port of the memcached server
     * 'persistent' => (bool) : use or not persistent connections to this memcached server
     * 'weight' => (int) : number of buckets to create for this server which in turn control its
     *                     probability of it being selected. The probability is relative to the total
     *                     weight of all servers.
     * 'timeout' => (int) : value in seconds which will be used for connecting to the daemon. Think twice
     *                      before changing the default value of 1 second - you can lose all the
     *                      advantages of caching if your connection is too slow.
     * 'retry_interval' => (int) : controls how often a failed server will be retried, the default value
     *                             is 15 seconds. Setting this parameter to -1 disables automatic retry.
     * 'status' => (bool) : controls if the server should be flagged as online.
     * 'failure_callback' => (callback) : Allows the user to specify a callback function to run upon
     *                                    encountering an error. The callback is run before failover
     *                                    is attempted. The function takes two parameters, the hostname
     *                                    and port of the failed server.
     *
     * =====> (boolean) compression :
     * true if you want to use on-the-fly compression
     *
     * =====> (boolean) compatibility :
     * true if you use old memcache server or extension
     *
     * @var array available options
     */
    protected $options = array(
        'servers' => array(array(
            'host'              => self::DEFAULT_HOST,
            'port'              => self::DEFAULT_PORT,
            'persistent'        => self::DEFAULT_PERSISTENT,
            'weight'            => self::DEFAULT_WEIGHT,
            'timeout'           => self::DEFAULT_TIMEOUT,
            'retry_interval'    => self::DEFAULT_RETRY_INTERVAL,
            'status'            => self::DEFAULT_STATUS,
            'failure_callback'  => self::DEFAULT_FAILURE_CALLBACK
        )),
        'compression'   => false,
        'compatibility' => false,
    );

    /**
     * Memcache object
     *
     * @var mixed memcache object
     */
    protected $memcache = null;

    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @throws \Exception
     * @return void
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('memcache')) {
            throw new \Exception('The memcache extension must be loaded for using this model !');
        }
        $this->memcache = new \memcache;
        $options = array_merge($this->options, $options);
        $value= $options['servers'];
        if (isset($value['host'])) {
            // in this case, $value seems to be a simple associative array (one server only)
            $value = array(0 => $value); // let's transform it into a classical array of associative arrays
        }
        $options['servers'] = $value;
        foreach ($options['servers'] as $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = static::DEFAULT_PORT;
            }
            if (!array_key_exists('persistent', $server)) {
                $server['persistent'] = static::DEFAULT_PERSISTENT;
            }
            if (!array_key_exists('weight', $server)) {
                $server['weight'] = static::DEFAULT_WEIGHT;
            }
            if (!array_key_exists('timeout', $server)) {
                $server['timeout'] = static::DEFAULT_TIMEOUT;
            }
            if (!array_key_exists('retry_interval', $server)) {
                $server['retry_interval'] = static::DEFAULT_RETRY_INTERVAL;
            }
            if (!array_key_exists('status', $server)) {
                $server['status'] = static::DEFAULT_STATUS;
            }
            if (!array_key_exists('failure_callback', $server)) {
                $server['failure_callback'] = static::DEFAULT_FAILURE_CALLBACK;
            }
            if ($options['compatibility']) {
                // No status for compatibility mode (#ZF-5887)
                $this->memcache->addServer($server['host'], $server['port'], $server['persistent'],
                                        $server['weight'], $server['timeout'],
                                        $server['retry_interval']);
            } else {
                $this->memcache->addServer($server['host'], $server['port'], $server['persistent'],
                                        $server['weight'], $server['timeout'],
                                        $server['retry_interval'],
                                        $server['status'], $server['failure_callback']);
            }
        }
    }

    public function getType()
    {
        return 'memcache';
    }

    public function getEngine()
    {
        return $this->memcache;
    }

    /**
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
     * @return mixed|false Cached datas
     */
    public function load($id)
    {
        $id = $this->prefix($id);
        return $this->memcache->get($id);
    }

    /**
     * Save some data in a key
     *
     * @param  mixed $data      Data to put in cache
     * @param  string $id       Store id
     * @return boolean True if no problem
     */
    public function save($data, $id, $ttl = 0)
    {
        $id = $this->prefix($id);
        if (!($result = $this->memcache->add($id, $data, $ttl))) {
            $result = $this->memcache->set($id, $data, $ttl);
        }
        return $result;
    }

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return boolean True if ok
     */
    public function remove($id)
    {
        $id = $this->prefix($id);
        return $this->memcache->delete($id);
    }

    /**
     * Clean cached entries
     *
     * @return boolean True if ok
     */
    public function flush()
    {
        return $this->memcache->flush();
    }
}
