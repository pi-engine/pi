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

class Bootstrap extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('bootstrap');
        $select = $model->select()->order('priority ASC, id ASC')->where(array('active' => 1));
        $rowset = $model->selectWith($select);

        $configs = array();
        foreach ($rowset as $row) {
            $module = $row->module;
            $directory = Pi::service('module')->directory($module);
            $class = sprintf('Module\\%s\\Bootstrap', ucfirst($directory));
            $configs[$module] = $class;
        }

        return $configs;
    }

    public function read()
    {
        return $this->loadData();
    }

    public function create()
    {
        $this->flush();
        $this->read();
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
