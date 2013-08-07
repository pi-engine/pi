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
 * Config cache
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
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
        $select = $modelConfig->select()
            ->columns(array('name', 'value', 'filter'))
            ->where($where);
        $rowset = $modelConfig->selectWith($select);
        $configs = array();
        foreach ($rowset as $row) {
            $configs[$row->name] = $row->value;
        }

        return $configs;
    }

    /**
     * {@inheritDoc}
     * @param string        $module
     * @param string|null   $category
     */
    public function read($module = '', $category = null)
    {
        $module = $module ?: 'system';
        if ('system' == $module && null === $category) {
            $category = 'general';
        }
        $options = compact('module', 'category');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string        $module
     * @param string|null   $category
     */
    public function create($module = '', $category = null)
    {
        $this->clear($module);
        $this->read($module, $category);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($namespace = '')
    {
        $namespace = $namespace ?: 'system';
        parent::clear($namespace);

        return $this;
    }
}
