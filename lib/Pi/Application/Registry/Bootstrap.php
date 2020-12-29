<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Module bootstrap list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Bootstrap extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $model  = Pi::model('bootstrap');
        $select = $model->select()->order('priority ASC, id ASC')->where(['active' => 1]);
        $rowset = $model->selectWith($select);

        $configs = [];
        foreach ($rowset as $row) {
            $module           = $row->module;
            $directory        = Pi::service('module')->directory($module);
            $class            = sprintf('Module\\%s\Bootstrap', ucfirst($directory));
            $configs[$module] = $class;
        }

        return $configs;
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        return $this->loadData();
    }

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $this->flush();
        $this->read();

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
