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

class Module extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $modules = array();
        $modelModule = Pi::model('module');
        $rowset = $modelModule->select(array());
        foreach ($rowset as $module) {
            $modules[$module->name] = array(
                'id'        => $module->id,
                'name'      => $module->name,
                'title'     => $module->title,
                'active'    => $module->active,
                'directory' => $module->directory,
            );
        }
        return $modules;
    }

    public function read($module = null)
    {
        $data = $this->loadData();
        $ret = empty($module)
                    ? $data
                    : (isset($data[$module])
                        ? $data[$module]
                        : false);
        return $ret;
    }

    public function create($module = null)
    {
        $this->clear($module);
        $this->read($module);
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
