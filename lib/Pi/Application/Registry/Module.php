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

/**
 * Module list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Module extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     * @param string $module
     */
    public function read($module = '')
    {
        $data = $this->loadData();
        $ret = empty($module)
                    ? $data
                    : (isset($data[$module])
                        ? $data[$module]
                        : false);

        return $ret;
    }

    /**
     * {@inheritDoc}
     * @param string $module
     */
    public function create($module = '')
    {
        $this->clear($module);
        $this->read($module);

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
