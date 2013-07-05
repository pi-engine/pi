<?php
/**
 * Pi ACL resource Model
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
use Pi\Application\Model\Nest;
use Pi\Db\RowGateway\Node;

class Resource extends Nest
{
    protected $section;
    protected $module;
    protected $table = 'acl_resource';

    public function setSection($section)
    {
        if (!is_null($section)) {
            $this->section = $section;
        }
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setModule($module)
    {
        if (!is_null($module)) {
            $this->module = $module;
        }
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get ancestors of a resource
     *
     * @param   mixed   $objective  resource ID or {Node}
     * @return  array   array of resources
     */
    public function getAncestors($resource, $cols = 'name')
    {
        if (!($resource instanceof Node)) {
            $resource = $this->find($resource);
            if (!$resource) {
                return false;
            }
        }
        $result = parent::getAncestors($resource, (array) $cols);
        $parents = array();
        foreach ($result as $row) {
            $parents[] = (is_string($cols) && $cols != '*') ? $row->$cols : $row->toArray();
        }
        return $parents;
    }

    /**
     * Remove a resource
     *
     * @param   mixed   $objective  resource ID or {Node}
     * @param   bool    $recursive  Whether to delete all children nodes
     * @return   int     affected rows
     */
    public function remove($resource, $recursive = false)
    {
        if (!($resource instanceof Node)) {
            if (!$resource = $this->find($resource)) {
                return false;
            }
        }
        $resources = array();
        if (empty($recursive)) {
            //$resources[$resource->id] = $resource->module;
        } else {
            $resources = array();
            if (!$list = $this->getChildren($resource, array('id', 'module'))) {
                return false;
            }
            foreach ($list as $row) {
                $resources[$row->id] = $row->module;
            }
        }
        $resources[$resource->id] = $resource->module;
        parent::remove($resource, $recursive);
        $modelRule = Pi::model('acl_rule');
        $modelRule->delete(array('section' => $resource->section, 'module' => $resource->module, 'resource' => array_keys($resources)));
        return true;
    }
}
