<?php
/**
 * Pi cache registry for admin access list
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

class Admin extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $rowset = Pi::model('module')->select(array('active' => 1));
        // Created page list indexed by module-controller-action
        $pages = array();
        foreach ($pageList as $page) {
            $key = $module;
            if (!empty($page['controller'])) {
                $key .= '-' . $page['controller'];
                if (!empty($page['action'])) {
                    $key .= '-' . $page['action'];
                }
            }
            $pages[$key] = $page['id'];
        }

        if (empty($pages)) {
            return array();
        }

        $modelLinks = Pi::model('page_block');
        $select = $modelLinks->select()->order(array('zone', 'order'))->where(array('page' => array_values($pages)));
        $blockLinks = $modelLinks->selectWith($select)->toArray();
        $blocksId = array();

        // Get all block Ids
        foreach ($blockLinks as $link) {
            $blocksId[$link['block']] = 1;
        }
        // Check for active for blocks
        if (!empty($blocksId)) {
            $modelBlock = Pi::model('block');
            $select = $modelBlock->select()->columns(array('id'))->where(array('id' => array_keys($blocksId), 'active' => 0));
            $rowset = $modelBlock->selectWith($select);
            foreach ($rowset as $row) {
                unset($blocksId[$row->id]);
            }
        }

        // Filter blocks via ACL check
        $blocksAllowed = null;
        if (null !== $role && $role != AclManager::ADMIN && !empty($blocksId)) {
            $acl = new AclManager('block');
            $where = Pi::db()->where(array('resource' => array_keys($blocksId)));
            /*
            // Get allowed blocks directly if default permssion as denied
            if (!$acl->getDefault()) {
                $blocksAllowed = $acl->getResources($where);
            // Get denied blocks and remove them if default permission as allowed
            } else {
                $blocksDenied = $acl->getResources($where, false);
                $blocksAllowed = array_diff(array_keys($blocksId), $blocksDenied);
            }
            */
            $blocksDenied = $acl->getResources($where, false);
            $blocksAllowed = array_diff(array_keys($blocksId), $blocksDenied);
        }

        // Reorganize blocks by page and zone
        $blocksByPageZone = array();
        foreach ($blockLinks as $link) {
            // Skip inactive blocks
            if (!isset($blocksId[$link['block']])) {
                continue;
            }
            if (null === $blocksAllowed || in_array($link['block'], $blocksAllowed)) {
                if (!isset($blocksByPageZone[$link['page']][$link['zone']])) {
                    $blocksByPageZone[$link['page']][$link['zone']] = array();
                }
                $blocksByPageZone[$link['page']][$link['zone']][] = $link['block'];
            }
        }

        foreach ($pages as $key => &$item) {
            if (isset($blocksByPageZone[$item])) {
                $item = $blocksByPageZone[$item];
            } else {
                $item = array();
            }
        }

        return $pages;
    }

    /**
     * Get permitted access list
     *
     * @param string $privilege manage or operation
     * @param string $module
     * @param string $role
     * @return array
     */
    public function read($privilege = 'manage', $module = null, $role = null)
    {
        if (null === $role) {
            $role = Pi::registry('user')->role;
        }
        $options = compact('privilege', 'role');
        $data = $this->loadData($options);
        if (isset($module)) {
            return isset($data[$module]) ? $data[$module] : array();
        }
        return $data;
    }

    public function create($privilege = 'manage', $role = null)
    {
        $this->clear();
        $this->read($privilege, $role);
        return true;
    }

    public function setNamespace($meta)
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
        $this->clear('');
        return $this;
    }
}
