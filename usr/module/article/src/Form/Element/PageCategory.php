<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;

/**
 * Category form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class PageCategory extends Category
{
    /**
     * Table name
     * @var string 
     */
    protected $table = 'category';
    
    /**
     * Read all added categories from database without root node
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        $options = parent::getValueOptions();
        $options = array_filter($options);
        $default[0] = _a('Null');
        if (!empty($options)) {
            $default['all'] = _a('All');
        }
        
        $this->valueOptions = $default + $options;
        
        return $this->valueOptions;
    }
}
