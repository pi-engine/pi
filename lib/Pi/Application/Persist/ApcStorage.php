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
use APCIterator;

class ApcStorage extends AbstractStorage
{
    public function __construct($options = array())
    {
        if (version_compare('3.1.6', phpversion('apc')) > 0) {
            throw new \Exception("Missing ext/apc >= 3.1.6");
        }

        $enabled = ini_get('apc.enabled');
        if (PHP_SAPI == 'cli') {
            $enabled = $enabled && (bool) ini_get('apc.enable_cli');
        }

        if (!$enabled) {
            throw new \Exception("ext/apc is disabled - see 'apc.enabled' and 'apc.enable_cli'");
        }
    }

    public function getType()
    {
        return 'apc';
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
        return apc_fetch($this->prefix($id));
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
        return apc_store($id, $data, $ttl);
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
        return apc_delete($id);
    }

    /**
     * Clear cached entries
     *
     * @return boolean True if ok
     */
    public function flush()
    {
        $regex  = '/^' . preg_quote($this->prefix(), '/') . '+/';
        return apc_delete(new APCIterator('user', $regex, 0, 1, \APC_LIST_ACTIVE));
    }
}
