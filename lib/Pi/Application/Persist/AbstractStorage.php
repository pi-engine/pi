<?php
/**
 * Kernel persist abstraction
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

abstract class AbstractStorage
{
    /**
     * Namespace for stored items
     * @var string
     */
    protected $namespace;

    abstract public function getType();

    /**
     * Set namespace
     *
     * @param string $namespace
     * @return StorageInterface
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
     * Test if an item is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Item id
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
     * @param  string $id       Store id
     * @param  int $ttl
     * @return boolean True if no problem
     */
    public function save($data, $id, $ttl = 0)
    {
        return;
    }

    /**
     * Remove an item
     *
     * @param  string $id Data id to remove
     * @return boolean True if ok
     */
    public function remove($id)
    {
        return;
    }

    /**
     * Clear cached entries
     *
     * @return boolean True if ok
     */
    public function flush()
    {
        return;
    }
}
