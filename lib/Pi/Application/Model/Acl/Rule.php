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
     * Get resources to which a group of roles is allowed/denied to access a given resource privilege
     *
     * @param array     $roles
     * @param array|Where    $where
     * @param boolean   $allowed allowed or denied
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
        $select = $this->select()->where($where)->columns(array(
            'resource',
            'denied' => Pi::db()->expression('SUM(' . $this->quoteIdentifier('deny') . ')')
        ));
        $select->group('resource');
        // allowed
        if (!empty($allowed)) {
            $select->having('denied = 0');
        // denied
        } else {
            $select->having('denied > 0');
        }
        $resources = array();
        $rowset = $this->selectWith($select);
        foreach ($rowset as $row) {
            $resources[] = $row->resource;
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
    public function isAllowed($where = null, $default = false)
    {
        if (!$where instanceof Where) {
            $where = Pi::db()->where($where);
        }
        $where->equalTo('section', $this->getSection());

        // For default as permitted: denied if deny is detected, otherwise permitted
        if ($default) {
            $where->equalTo('deny', 1);
            $select = $this->select()->Where($where)->limit(1);
            $rowset = $this->selectWith($select);
            $permission = $rowset->count() ? false : true;
        // For default as denied: denied if deny is detected or no rule is found, otherwise permitted
        } else {
            $columns = array(
                'denied'    => Pi::db()->expression('SUM(' . $this->quoteIdentifier('deny') . ')'),
                'count'     => Pi::db()->expression('count(*)'),
            );
            $select = $this->select()->Where($where)->columns($columns);
            $row = $this->selectWith($select)->current();
            $permission = ($row->denied || !$row->count) ? false : true;
        }

        /*
        $columns = array('denied' => Pi::db()->expression('SUM(' . $this->quoteIdentifier('deny') . ')'));
        if (!$default) {
            $columns['count'] = Pi::db()->expression('count(*)');
        }
        $select = $this->select()->Where($where)->columns($columns);
        $row = $this->selectWith($select)->current();

        $permission = true;
        // Denied if at least one deny is detected
        if ($row->denied) {
            $permission = false;
        // Denied if default as false and no rule is detected
        } elseif (!$default && !$row->count) {
            $permission = false;
        }
        */

        return $permission;
    }
}
