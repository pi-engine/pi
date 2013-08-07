<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

/**
 * Gateway for persist handlers
 *
 * The class is supposed to serve system only, i.e. not called by modules
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Persist
{
    /**
     * Currently active persist handler
     *
     * @var Persist\AbstractStorage
     */
    protected $handler;

    /**
     * Backend storage of currently active persist handler,
     * potential types: Apc, Memcached, Memcache, Redis, File, etc.
     *
     * @var string
     */
    protected $storage;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $storage = ucfirst($config['storage']);
        $this->handler = $this->loadHandler($storage, isset($config['options'])
            ? $config['options'] : array());
        if (!$this->handler) {
            throw new \DomainException(
                sprintf('Storage "%s" is not supported.', $storage)
            );
        }
        $this->storage = $storage;
        $this->handler->setNamespace($config['namespace']);
    }

    /**
     * Loads a backend handler
     *
     * @param string    $storage
     * @param array     $options
     * @return Persist\AbstractStorage|false
     */
    public function loadHandler($storage, $options = array())
    {
        $class = __NAMESPACE__ . '\\'
               . sprintf('Persist\\%sStorage', $storage);
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
     * @return Persist\AbstractStorage|false
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
        return (!empty($this->type) && $this->type != "Filesystem")
            ? true : false;
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
    /**
     * Load an entity
     *
     * @param string $id
     * @return mixed
     * @see Persist\AbstractStorage::load()
     */
    public function load($id)
    {
        return $this->handler->load($id);
    }

    /**
     * Save an entity
     *
     * @param mixed $data
     * @param string $id
     * @param int $ttl
     * @return void
     * @see Persist\AbstractStorage::save()
     */
    public function save($data, $id, $ttl = 0)
    {
        return $this->handler->save($data, $id, $ttl);
    }

    /**
     * Remove an entity
     *
     * @param string $id
     * @return bool
     * @see Persist\AbstractStorage::remove()
     */
    public function remove($id)
    {
        return $this->handler->remove($id);
    }

    /**
     * Flush
     *
     * @return bool
     * @see Persist\AbstractStorage::flush()
     */
    public function flush()
    {
        $this->handler->flush();
    }
    /**#@-*/

    /**
     * Magic methods call {@link Persist\AbstractStorage}
     *
     * @param string    $method
     * @param array     $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (!$this->handler) {
            return false;
        }
        return call_user_func_array(array($this->handler, $method), $params);
    }
}
