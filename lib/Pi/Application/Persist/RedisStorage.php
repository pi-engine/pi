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
use Redis;

class RedisStorage extends AbstractStorage
{
    /**
     * Server Values
     */
    const SERVER_HOST = '127.0.0.1';
    const SERVER_PORT =  6379;
    const SERVER_TIMEOUT =  0;

    protected $redis;

    public function __construct($options = array())
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('The redis extension must be loaded for using this model !');
        }
        $redis = new Redis;
        $status = $redis->connect(
            isset($options['host']) ? $options['host'] : static::SERVER_HOST,
            isset($options['port']) ? $options['port'] : static::SERVER_PORT,
            isset($options['timeout']) ? $options['timeout'] : static::SERVER_TIMEOUT
        );
        if (!$status) {
            throw new \Exception('The redis server connection failed.');
        }
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);   // use igBinary serialize/unserialize
        $this->redis = $redis;
    }

    public function getType()
    {
        return 'redis';
    }

    public function getEngine()
    {
        return $this->redis;
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
        $data = $this->redis->get($id);

        return $data;
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
        $this->redis->sadd($this->namespace, $id);
        /*
        if ((is_string($data) && !is_numeric($data)) || is_object($data) || is_array($data)) {
            $data = serialize($data);
        }
        */
        if ($ttl) {
            $result = $this->redis->setex($id, $data, $ttl);
        } else {
            $result = $this->redis->set($id, $data);
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
        return $this->redis->delete($id);
    }

    /**
     * Clear cached entries
     *
     * @return boolean True if ok
     */
    public function flush()
    {
        $members = $this->redis->sMembers($this->namespace);
        $multi = $this->redis->multi();
        foreach ($members as $id) {
            $multi->delete($id);
            $this->redis->sRem($this->namespace, $id);
        }
        $multi->exec();

        return true;
    }
}
