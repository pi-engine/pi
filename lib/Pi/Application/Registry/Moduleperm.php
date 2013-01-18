<?php
/**
 * Pi cache registry
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
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;
use Pi\Acl\Acl as AclManager;

class Moduleperm extends AbstractRegistry
{
    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: front, admin, manage
     */
    protected function loadDynamic($options)
    {
        $aclHandler = new AclManager('module-' . $options['type']);
        $modules = $aclHandler->setRole($options['role'])->getResources();

        return $modules;
    }

    /**
     * Get allowed module list
     *
     * @param string $type      default as front
     *                              front: all active and allowed for front section
     *                              admin: all active and allowed for admin section
     *                              operation: all active and allowed for admin operations
     *                              manage: all active and allowed for admin managed components
     *
     * @param string $role
     * @return array
     */
    public function read($type = 'front', $role = null)
    {
        //$this->cache = false;
        if (null === $role) {
            $role = Pi::registry('user')->role();
        }
        $options = compact('type', 'role');
        return $this->loadData($options);
    }

    public function create($type = 'front', $role = null)
    {
        $this->clear('');
        $this->read($type, $role);
        return true;
    }

    public function setNamespace($meta = null)
    {
        return parent::setNamespace('');
    }

    public function clear($namespace = '')
    {
        parent::clear('');
        return $this;
    }

    public function flush()
    {
        return $this->clear('');
    }
}
