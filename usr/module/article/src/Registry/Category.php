<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Category registry class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Category extends AbstractRegistry
{
    protected $module = 'article';

    /**
     * Load data from database
     *
     * @param array $options
     * @return array
     */
    protected function loadDynamic($options = [])
    {
        $module = $options['module'];
        $model  = Pi::model('category', $module);

        if ($options['isTree']) {
            $root   = $model->find('root', 'name');
            $rowset = $model->enumerate($root->id);
            $rows   = array_shift($rowset);
        } else {
            $rows = $model->getList();
        }

        return $rows;
    }

    /**
     * Read data from cache or database
     *
     * @return array
     */
    public function read($where = [], $isTree = false, $module = null)
    {
        $module  = $module ?: Pi::service('module')->current();
        $options = compact('module', 'where', 'isTree');

        return $this->loadData($options);
    }

    /**
     * Create a cache
     */
    public function create($where = [], $isTree = false)
    {
        $module = Pi::service('module')->current();
        $this->clear($module);
        $this->read($where, $isTree);
    }

    /**
     * Clear cache
     *
     * @param string $namespace
     * @return \Module\Article\Registry\Category
     */
    public function clear($namespace = '')
    {
        parent::clear($namespace);
        return $this;
    }

    /**
     * Flush all cache
     *
     * @return \Module\Article\Registry\Category
     */
    public function flush()
    {
        $module = Pi::service('module')->current();
        $this->clear($module);
        return $this;
    }
}
