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

class Search extends AbstractRegistry
{
    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: active, inactive, all
     * @return  array   keys: dirname => callback, active
     */
    protected function loadDynamic($options)
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

    public function read($active = true)
    {
        $options = compact('active');
        return $this->loadData($options);
    }

    public function create($active = true)
    {
        $this->clear('');
        $this->read($active);
        return true;
    }

    public function setNamespace($meta)
    {
        return parent::setNamespace('');
    }

    public function flush()
    {
        return $this->clear('');
    }
}
