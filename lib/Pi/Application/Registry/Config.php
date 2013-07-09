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

class Config extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $module = '';
        if (!empty($options['module'])) {
            if (!Pi::service('module')->isActive($options['module'])) {
                return false;
            }
            $module = $options['module'];
        }
        $category = null;
        if (isset($options['category'])) {
            $category = $options['category'];
        }

        $modelConfig = Pi::model('config');
        $where = array('module' => $module);
        if (isset($category)) {
            $where['category'] = $category;
        }
        $select = $modelConfig->select()->columns(array('name', 'value', 'filter'))->where($where);
        $rowset = $modelConfig->selectWith($select);
        $configs = array();
        foreach ($rowset as $row) {
            $configs[$row->name] = $row->value;
        }

        return $configs;
    }

    public function read($module, $category = null)
    {
        $module = $module ?: 'system';
        if ('system' == $module && null === $category) {
            $category = 'general';
        }
        $options = compact('module', 'category');
        return $this->loadData($options);
    }

    public function create($module, $category = null)
    {
        $this->clear($module);
        $this->read($module, $category);
        return true;
    }

    public function clear($namespace = '')
    {
        $namespace = $namespace ?: 'system';
        parent::clear($namespace);
        return $this;
    }
}
