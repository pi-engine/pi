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
 * Extended registry class
 * 
 * Feature list:
 * 
 * 1. Read table fields
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Extended extends AbstractRegistry
{
    protected $module = 'article';

    /**
     * Load data from database
     * 
     * @param array $options
     * @return array 
     */
    protected function loadDynamic($options = array())
    {
        $module = $options['module'];
        $model  = Pi::model('extended', $module);
        
        $rows   = $model->getValidColumns();

        return $rows;
    }
    
    /**
     * Read data from cache or database
     * 
     * @return array 
     */
    public function read($module = null)
    {
        $module  = $module ?: Pi::service('module')->current();
        $options = compact('module');
        
        return $this->loadData($options);
    }
    
    /**
     * Create a cache
     */
    public function create()
    {
        $module  = Pi::service('module')->current();
        $this->clear($module);
        $this->read();
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
