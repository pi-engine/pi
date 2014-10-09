<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;

/**
 * Public API for other module
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Cluster extends Category
{
    /**
     * Table name
     * @var string 
     */
    protected $table = 'cluster';
    
    /**
     * Load custom cluster forms configuration
     * 
     * @return array
     */
    public function loadConfig()
    {
        $filename = sprintf(
            '%s/module/%s/config/cluster.form.php',
            Pi::path('custom'),
            $this->module
        );
        if (!file_exists($filename)) {
            $filename = sprintf(
                '%s/article/config/cluster.form.php',
                Pi::path('module')
            );
        }
        $config = include $filename;
        
        return $config;
    }
    
    /**
     * Get custom cluster form name
     * 
     * @return array
     */
    public function getFields()
    {
        $fields = array();
        
        $config = $this->loadConfig();
        foreach ($config['field'] as $field => $val) {
            $fields[] = isset($val['name']) ? $val['name'] : $field;
        }
        
        return $fields;
    }
}