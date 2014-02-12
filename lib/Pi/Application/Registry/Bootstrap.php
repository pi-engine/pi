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
 * Module bootstrap list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Bootstrap extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('bootstrap');
        $select = $model->select()->order('priority ASC, id ASC')
            ->where(array('active' => 1));
        $rowset = $model->selectWith($select);

        $configs = array();
        foreach ($rowset as $row) {
            $module = $row->module;
            $directory = Pi::service('module')->directory($module);
            $class = sprintf('Module\\%s\Bootstrap', ucfirst($directory));
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
