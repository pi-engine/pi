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
class MemcachedStorage extends AbstractStorage
{
    /**
     * Server Values
     */
    const SERVER_HOST = '127.0.0.1';
    const SERVER_PORT =  11211;
    const SERVER_WEIGHT  = 1;

    /**
     * Memcached object
     *
     * @var mixed memcached object
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
            throw new \Exception('The memcached extension must be loaded for using this model !');
        }
        $this->memcached = new \memcached;
        $this->memcached->addServer(
            isset($options['host']) ? $options['host'] : static::SERVER_HOST,
            isset($options['port']) ? $options['port'] : static::SERVER_PORT,
            isset($options['weight']) ? $options['weight'] : static::SERVER_WEIGHT
        );
    }

    public function getType()
    {
        return 'memcached';
    }

    public function getEngine()
    {
        return $this->memcached;
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
        return $this->memcached->get($id);
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
        if (!($result = $this->memcached->add($id, $data, $ttl))) {
            $result = $this->memcached->set($id, $data, $ttl);
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
        return $this->memcached->delete($id);
    }

    /**
     * Clear cached entries
     *
     * @return boolean True if ok
     */
    public function flush()
    {
        return $this->memcached->flush();
    }
}
