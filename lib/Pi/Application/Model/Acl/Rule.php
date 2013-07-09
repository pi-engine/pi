<?php
/**
 * Pi ACL Rule Model
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
 * @subpackage      Model
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Model\Acl;

use Pi;
use Pi\Application\Model\Model;
use Zend\Db\Sql\Predicate\Predicate;

class Rule extends Model
{
    protected $section = '';
    protected $table = 'acl_rule';

    public function setSection($section)
    {
        if (null !== $section) {
            $this->section = $section;
        }
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getRules($roles = array(), $resources = array(), $privilege = null)
    {
        $where = Pi::db()->where()->equalTo('section', $this->getSection());
        if (!empty($roles)) {
            if (count($roles) == 1) {
                $where->equalTo('role', array_shift($roles));
            } else {
                $where->in('role', $roles);
            }
        }
        if (!empty($resources)) {
            if (count($resources) == 1) {
                $where->equalTo('resource', array_shift($resources));
            } else {
                $where->in('resource', $resources);
            }
        }
        if (null !== $privilege) {
            $where->equalTo('privilege', $privilege);
        }
        $rowset = $this->select($where);
        return $rowset;
    }

    /**
     * Get resources to which a group of roles is allowed to access a given resource privilege
     *
     * @param array         $roles
     * @param array|Where   $where
     * @param bool          $allowed
     * @return array of resources
     */
    public function getResources($roles, $where = null, $allowed = true)
    {
        if (!$where instanceof Predicate) {
            $where = Pi::db()->where((array) $where);
        }
        $predicate = $where->nest();
        $predicate->equalTo('section', $this->getSection());
        if (count($roles) > 1) {
            $predicate->in('role', $roles);
        } else {
            $predicate->equalTo('role', array_shift($roles));
        }
        $predicate->equalTo('deny', $allowed ? 0 : 1);
        $columns = array(
            'item' => Pi::db()->expression('DISTINCT resource')
        );
        $select = $this->select()->where($where)->columns($columns);
        $resources = array();
        $rowset = $this->selectWith($select);
        foreach ($rowset as $row) {
            $resources[] = $row->item;
        }
        return $resources;
    }

    /**
     * Check if a group of roles is allowed/denied to access a given resource privilege
     *
     * @param array|Where   $where
     * @param bool          $default Default permission in case not defined
     * @return boolean
     */
    public function isAllowed($where = null, $default = null)
    {
        if (!$where instanceof Where) {
            $where = Pi::db()->where($where);
        }
        $where->equalTo('section', $this->getSection());

        // Check if permitted
        $wherePermitted = clone $where;
        $wherePermitted->equalTo('deny', 0);
        $select = $this->select()->Where($wherePermitted)->limit(1);
        $rowset = $this->selectWith($select);
        // Permitted if deny = 0 detected
        if ($rowset->count()) {
            $permission = true;
        // Denied if deny = 1 detected, otherwise, default
        } else {
            // Check if denied
            $where->equalTo('deny', 1);
            $select = $this->select()->Where($where)->limit(1);
            $rowset = $this->selectWith($select);
            $permission = $rowset->count() ? false : $default;
        }

        return $permission;
    }
}
