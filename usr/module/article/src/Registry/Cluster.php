<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Registry;

use Pi;

/**
 * Cluster registry class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Cluster extends Category
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
        $model  = Pi::model('cluster', $module);
        
        if ($options['isTree']) {
            $root   = $model->find('root', 'name');
            $rowset = $model->enumerate($root->id);
            $rows   = array_shift($rowset);
        } else {
            $rows   = $model->getList();
        }

        return $rows;
    }
}
