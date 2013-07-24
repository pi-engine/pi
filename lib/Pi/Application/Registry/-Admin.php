<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Registry;

use Pi;
use Pi\Acl\Acl as AclManager;

/**
 * Permitted access list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Admin extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     * @return array
     */
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
     * {@inheritDoc}
     * @param string        $privilege manage or operation
     * @param string|null   $module
     * @param string|null   $role
     */
    public function read($privilege = 'manage', $module = null, $role = null)
    {
        if (null === $role) {
            $role = Pi::service('user')->getUser()->role();
        }
        $options = compact('privilege', 'role');
        $data = $this->loadData($options);
        if (isset($module)) {
            return isset($data[$module]) ? $data[$module] : array();
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string        $privilege
     * @param string|null   $role
     */
    public function create($privilege = 'manage', $role = null)
    {
        $this->clear();
        $this->read($privilege, $role);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta)
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function clear($namespace = '')
    {
        parent::clear('');
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->clear('');
        return $this;
    }
}
