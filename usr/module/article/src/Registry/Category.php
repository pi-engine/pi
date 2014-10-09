<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Registry;

use Pi\Application\Registry\AbstractRegistry;
use Pi;

/**
 * Category registry class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Category extends AbstractRegistry
{
    protected $module = 'article';
    
    /**
     * Table name
     * @var string 
     */
    protected $table = 'category';

    /**
     * Load data from database
     * 
     * @param array $options
     * @return array 
     */
    protected function loadDynamic($options = array())
    {
        $module = $options['module'];
        $model  = Pi::model($this->table, $module);
        
        if (!$options['plain']) {
            $root   = $model->find('root', 'name');
            $rowset = $model->enumerate($root->id);
            $rows   = array_shift($rowset);
        } else {
            $rows   = $model->enumerate(null, null, true);
        }

        return $rows;
    }
    
    /**
     * Read data from cache or database
     * 
     * @param bool $plain  Whether to read categories with tree structure
     * @return array 
     */
    public function read($plain = true, $module = null)
    {
        $module  = $module ?: Pi::service('module')->current();
        $options = compact('module', 'plain');
        
        return $this->loadData($options);
    }
    
    /**
     * Create a cache
     */
    public function create($isTree = false)
    {
        $module  = Pi::service('module')->current();
        $this->clear($module);
        $this->read($isTree);
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
