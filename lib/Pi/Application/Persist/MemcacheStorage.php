<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Persist;

use Pi;

/**
 * Memcache storage
 *
 * This storage does not support namespace or tag
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MemcacheStorage extends AbstractStorage
{
    /**
     * Default Values
     */
    /** @var string */
    const DEFAULT_HOST = '127.0.0.1';

    /** @var int */
    const DEFAULT_PORT =  11211;

    /** @var bool */
    const DEFAULT_PERSISTENT = true;

    /** @var int */
    const DEFAULT_WEIGHT  = 1;

    /** @var int */
    const DEFAULT_TIMEOUT = 1;

    /** @var int */
    const DEFAULT_RETRY_INTERVAL = 15;

    /** @var bool */
    const DEFAULT_STATUS = true;

    /** @var null|Callback */
    const DEFAULT_FAILURE_CALLBACK = null;

    /**
     * Available options
     *
     * =====> (array) servers :
     * an array of memcached server;
     * each memcached server is described by an associative array :
     *
     * - 'host' => (string) : the name of the memcached server
     * - 'port' => (int) : the port of the memcached server
     * - 'persistent' => (bool) :
     *      use or not persistent connections to this memcached server
     * - 'weight' => (int) :
     *      number of buckets to create for this server which in turn control
     *      its probability of it being selected. The probability is relative
     *      to the total weight of all servers.
     * - 'timeout' => (int) :
     *      value in seconds which will be used for connecting to the daemon.
     *      Think twice before changing the default value of 1 second -
     *      you can lose all the dvantages of caching if your connection is
     *      too slow.
     * - 'retry_interval' => (int) :
     *      controls how often a failed server will be retried,
     *      the default value is 15 seconds.
     *      Setting this parameter to -1 disables automatic retry.
     * - 'status' => (bool) :
     *      controls if the server should be flagged as online.
     * - 'failure_callback' => (callback) :
     *      Allows the user to specify a callback function to run
     *      upon encountering an error. The callback is run
     *      before failover is attempted. The function takes two parameters,
     *      the hostname and port of the failed server.
     *
     * =====> (boolean) compression :
     *
     * - true if you want to use on-the-fly compression
     *
     * =====> (boolean) compatibility :
     *
     * - true if you use old memcache server or extension
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
     * @var \memcache|null memcache object
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
            throw new \Exception(
                'The memcache extension must be loaded for using this model !'
            );
        }
        $this->memcache = new \memcache;
        $options = array_merge($this->options, $options);
        $value= $options['servers'];
        if (isset($value['host'])) {
            // Transform into a classical array of associative arrays
            $value = array(0 => $value);
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
                $this->memcache->addServer(
                    $server['host'],
                    $server['port'],
                    $server['persistent'],
                    $server['weight'],
                    $server['timeout'],
                    $server['retry_interval']
                );
            } else {
                $this->memcache->addServer(
                    $server['host'],
                    $server['port'],
                    $server['persistent'],
                    $server['weight'],
                    $server['timeout'],
                    $server['retry_interval'],
                    $server['status'],
                    $server['failure_callback']
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'memcache';
    }

    /**
     * {@inheritDoc}
     */
    public function getEngine()
    {
        return $this->memcache;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $id = $this->prefix($id);

        return $this->memcache->get($id);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $id = $this->prefix($id);

        return $this->memcache->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->memcache->flush();
    }
}
