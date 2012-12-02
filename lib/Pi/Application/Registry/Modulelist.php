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
use Pi\Acl\Acl as AclManager;

class Modulelist extends AbstractRegistry
{
    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: installed, active, inactive, install
     * @return  array    keys: dirname, name, (mid), weight, image, author, version
     */
    protected function loadDynamic($options)
    {
        $modules = array();
        $modelModule = Pi::model('module');
        $where = array();
        if ($options['type'] == 'active') {
            $where = array('active' => 1);
        } elseif ($options['type'] == 'inactive') {
            $where = array('active' => 0);
        }
        $rowset = $modelModule->select($where);
        foreach ($rowset as $module) {
            $info = Pi::service('module')->loadMeta($module->directory, 'meta');
            $modules[$module->name] = array(
                'id'            => $module->id,
                'name'          => $module->name,
                'title'         => $module->title,
                'active'        => $module->active,
                'version'       => $module->version,
                'directory'     => $module->directory,
                'update'        => $module->update,
                'logo'          => $info['logo'],
            );
        }
        return $modules;
    }

    /*
    protected function loadData(&$options = array())
    {
        if (false === ($data = $this->loadCache($options))) {
            $data = $this->loadDynamic($options);
            $this->saveCache($data, $options);
        }
        return $data;
    }
    */

    public function read($type = null/*, $section = null, $role = null*/)
    {
        $options = compact('type'/*, 'section', 'role'*/);
        return $this->loadData($options);
    }

    public function create($type = null/*, $section = null, $role = null*/)
    {
        $this->clear('');
        $this->read($type/*, $section, $role*/);
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
