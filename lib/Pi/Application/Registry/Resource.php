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
        if (!is_null($options['module'])) {
            $where['module'] = $options['module'];
        }
        $rowset = $model->select($where);
        if (!$rowset->count()) {
            return $ancestors;
        }
        foreach ($rowset as $row) {
            $ancestors[$row->name] = $model->getAncestors($row, 'id');
        }
        return $ancestors;
    }

    public function read($section, $module = null)
    {
        $options = compact('section', 'module');
        return $this->loadData($options);
    }

    public function create($section, $module = null)
    {
        $options = compact('section', 'module');
        $this->read($module, $section);
        return true;
    }

    public function setNamespace($meta)
    {
        if (is_string($meta)) {
            $namespace = $meta;
        } else {
            $namespace = $meta['section'];
        }
        return parent::setNamespace($namespace);
    }

    public function flush()
    {
        $this->flushBySections();
        return $this;
    }
}
