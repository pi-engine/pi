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
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application;

/**
 * Gateway for persist handlers
 */
class Persist
{
    /**
     * Currently active persist handler
     */
    protected $handler;

    /**
     * Backend storage of currently active persist handler, potential types: Apc, Memcached, Memcache, Redis, File, etc.
     */
    protected $storage;

    /**
     * Constructor
     *
     * @param array $config
     * @return void
     */
    public function __construct($config = array())
    {
        $storage = ucfirst($config['storage']);
        $this->handler = $this->loadHandler($storage, isset($config['options']) ? $config['options'] : array());
        if (!$this->handler) {
            throw new \DomainException(sprintf('Storage "%s" is not supported.', $storage));
        }
        $this->storage = $storage;
        $this->handler->setNamespace($config['namespace']);
    }

    /**
     * Loads a backend handler
     *
     * @param string    $storage
     * @param array     $options
     * @return Persist\StorageInterface|false
     */
    public function loadHandler($storage, $options = array())
    {
        $class = __NAMESPACE__ . '\\' . sprintf('Persist\\%sStorage', $storage);
        try {
            $handler = new $class($options);
        } catch (\Exception $e) {
            $handler = false;
        }
        return $handler;
    }

    /**
     * Gets currently active backend handler
     *
     * @return {Persist\StorageInterface}|false
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Checks if there is valid backend available
     *
     * @return bool
     */
    public function isValid()
    {
        return (!empty($this->type) && $this->type != "Filesystem") ? true : false;
    }

    /**
     * Gets backend storage
     *
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**#@+
     * Persist APIs, proxy to handler
     * @see Persist\AbstractStorage
     */
    public function load($id)
    {
        return $this->handler->load($id);
    }

    public function save($data, $id, $ttl = 0)
    {
        return $this->handler->save($data, $id, $ttl);
    }

    public function remove($id)
    {
        return $this->handler->remove($id);
    }

    public function flush()
    {
        $this->handler->flush();
    }
    /**#@-*/

    /**
     * @see Persist\AbstractStorage
     */
    public function __call($method, $params)
    {
        if (!$this->handler) {
            return false;
        }
        return call_user_func_array(array($this->handler, $method), $params);
    }
}
