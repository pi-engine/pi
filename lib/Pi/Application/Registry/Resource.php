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

class Resource extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $ancestors = array();
        $model = Pi::model('acl_resource')->setSection($options['section']);
        $where = array('section' => $options['section']);
        $where['module'] = $options['module'];
        if (null !== $options['type']) {
            $where['type'] = $options['type'];
        }
        $rowset = $model->select($where);
        if (!$rowset->count()) {
            return $ancestors;
        }
        foreach ($rowset as $row) {
            $ancestors[$row->name] = $model->getAncestors($row, 'id');
            /*
            if (!empty($options['self'])) {
                $ancestors[$row->name][] = $row->id;
            }
            */
        }
        return $ancestors;
    }

    /**
     * Get all resources with specific section, module and type
     *
     * @param string $section   front, admin, module
     * @param string $module
     * @param string $type      system, page or other custom types by module
     * @param bool $self    Including self
     * @return array
     */
    public function read($section, $module, $type = null)
    {
        //$this->cache = false;
        $options = compact('section', 'module', 'type');
        return $this->loadData($options);
    }

    public function create($section, $module, $type = null)
    {
        $this->clear($module);
        $this->read($module, $section, $type);
        return true;
    }

    public function flush()
    {
        $this->clear('');
        $this->flushByModules();
        return $this;
    }
}
