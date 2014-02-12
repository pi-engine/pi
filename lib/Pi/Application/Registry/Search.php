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
 * Module search callback list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Search extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     * @param   array   $options    Potential values for type:
     *      active, inactive, all
     * @return  array   Keys: dirname => callback, active
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('search');

        if (!empty($options['active'])) {
            $where['active'] = 1;
        } elseif (null !== $options['active']) {
            $where['active'] = 0;
        }
        $rowset = $model->select($where);

        $modules = array();
        foreach ($rowset as $row) {
            $modules[$row->module] = $row->callback;
        }

        return $modules;
    }

    /**
     * {@inheritDoc}
     * @param bool $active
     */
    public function read($active = true)
    {
        $options = compact('active');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param bool $active
     */
    public function create($active = true)
    {
        $this->clear('');
        $this->read($active);

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
    public function flush()
    {
        return $this->clear('');
    }
}
