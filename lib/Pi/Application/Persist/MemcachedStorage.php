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
 * Memcached storage
 *
 * Note: this storage does not support namespace or tag
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MemcachedStorage extends AbstractStorage
{
    /**
     * Server Values
     */
    /** @var string */
    const SERVER_HOST = '127.0.0.1';

    /** @var int */
    const SERVER_PORT =  11211;

    /** @var int */
    const SERVER_WEIGHT  = 1;

    /**
     * Memcached object
     *
     * @var \memcached|null memcached object
     */
    protected $memcached;

    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @throws \Exception
     * @return void
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('memcached')) {
            throw new \Exception(
                'The memcached extension must be loaded for using this model !'
            );
        }
        $this->memcached = new \memcached;
        $this->memcached->addServer(
            isset($options['host']) ? $options['host'] : static::SERVER_HOST,
            isset($options['port']) ? $options['port'] : static::SERVER_PORT,
            isset($options['weight'])
                ? $options['weight'] : static::SERVER_WEIGHT
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'memcached';
    }

    /**
     * {@inheritDoc}
     */
    public function getEngine()
    {
        return $this->memcached;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $id = $this->prefix($id);

        return $this->memcached->get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id, $ttl = 0)
    {
        $id = $this->prefix($id);
        if (!($result = $this->memcached->add($id, $data, $ttl))) {
            $result = $this->memcached->set($id, $data, $ttl);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $id = $this->prefix($id);
        
        return $this->memcached->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->memcached->flush();
    }
}
