<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\Acl;

use Pi;
use Pi\Application\Model\Nest;
use Pi\Db\RowGateway\Node;

/**
 * ACL resource model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Resource extends Nest
{
    /**
     * Section name
     *
     * @var string
     */
    protected $section;

    /** @var string Module name */
    protected $module;

    /** @var string Table name */
    protected $table = 'acl_resource';

    /**
     * Set section name
     *
     * @param string $section
     * @return $this
     */
    public function setSection($section)
    {
        if (!is_null($section)) {
            $this->section = $section;
        }

        return $this;
    }

    /**
     * Get section name
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set module name
     *
     * @param string $module
     * @return $this
     */
    public function setModule($module)
    {
        if (!is_null($module)) {
            $this->module = $module;
        }

        return $this;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get ancestors of a resource
     *
     * @param int|Node      $resource   Resource ID or Node
     * @param string|array  $cols       Columns to load
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
            $parents[] = (is_string($cols) && $cols != '*')
                ? $row->$cols : $row->toArray();
        }

        return $parents;
    }

    /**
     * Remove a resource
     *
     * @param int|Node  $resource   Resource ID or Node
     * @param bool      $recursive  Whether to delete all children nodes
     * @return int Affected rows
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
            $list = $this->getChildren($resource, array('id', 'module'));
            if (!$list) {
                return false;
            }
            foreach ($list as $row) {
                $resources[$row->id] = $row->module;
            }
        }
        $resources[$resource->id] = $resource->module;
        parent::remove($resource, $recursive);
        $modelRule = Pi::model('acl_rule');
        $modelRule->delete(array(
            'section'   => $resource->section,
            'module'    => $resource->module,
            'resource'  => array_keys($resources)
        ));

        return true;
    }
}
