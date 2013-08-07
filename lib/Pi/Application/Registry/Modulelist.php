<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;
use Pi\Acl\Acl as AclManager;

/**
 * Module list of different types
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Modulelist extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     * @param   array  $options potential values for type: active, inactive
     * @return  array
     *  - keys: directory, name, title, id, logo, version, update
     */
    protected function loadDynamic($options)
    {
        $modules = array();
        $modelModule = Pi::model('module');
        $where = array();
        if ($options['type'] == 'inactive') {
            $where = array('active' => 0);
        } else {
            $where = array('active' => 1);
        }
        $rowset = $modelModule->select($where);
        foreach ($rowset as $module) {
            $info = Pi::service('module')->loadMeta($module->directory,
                'meta');
            $modules[$module->name] = array(
                'id'            => $module->id,
                'name'          => $module->name,
                'title'         => $module->title,
                //'active'        => $module->active,
                'version'       => $module->version,
                'directory'     => $module->directory,
                'update'        => $module->update,
                'logo'          => isset($info['logo']) ? $info['logo'] : '',
            );
        }

        asort($modules);
        if (isset($modules['system'])) {
            $systemModule = array('system' => $modules['system']);
            unset($modules['system']);
            $modules = array_merge($systemModule, $modules);
        }

        return $modules;
    }

    /**
     * {@inheritDoc}
     * @param string $type Default as active:
     *                              active - all active modules;
     *                              inactive - all inactive modules.
     */
    public function read($type = 'active')
    {
        //$this->cache = false;
        $options = compact('type');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $type Default as active:
     *                              active - all active modules;
     *                              inactive - all inactive modules.
     */
    public function create($type = 'active')
    {
        $this->clear('');
        $this->read($type);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function clear($namespace = '')
    {
        parent::clear('');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->clear('');
    }
}
