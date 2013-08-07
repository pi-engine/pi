<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Persist;

/**
 * Abstract class for persist storage
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractStorage
{
    /**
     * Namespace for stored items
     *
     * @var string
     */
    protected $namespace;

    /**
     * Get storage type
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Get storage engine
     *
     * @return Resource|null
     */
    public function getEngine()
    {
        return null;
    }

    /**
     * Set namespace
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Gets prefixed key or just the global namespace if key is not set
     *
     * @param string|null $key
     * @return string
     */
    protected function prefix($key = null)
    {
        if (null === $key) {
            return $this->namespace;
        }

        return $this->namespace . '.' . $key;
    }

    /**
     * Test if an item is available for the given id
     *
     * @param  string  $id Item id
     * @return mixed|false Cached datas
     */
    public function load($id)
    {
        return;
    }

    /**
     * Save some data in a key
     *
     * @param  mixed $data      Data to put in cache
     * @param  string $id       Stored id
     * @param  int $ttl
     * @return bool
     */
    public function save($data, $id, $ttl = 0)
    {
        return;
    }

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return bool
     */
    public function remove($id)
    {
        return;
    }

    /**
     * Clear cached entries
     *
     * @return bool
     */
    public function flush()
    {
        return;
    }
}
