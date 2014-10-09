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
use Zend\Form\Element\Select;

/**
 * Category form class for extending category selection
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Category extends Select
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
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
            $withRoot = (bool) $this->getOption('root') ?: false;
            $method   = sprintf('get%sList', ucfirst($this->table));
            $rowset   = Pi::api('api', $module)->$method(
                array('active' => 1),
                array('id', 'title', 'depth'),
                true,
                $withRoot
            );
            $result = array();
            foreach ($rowset as &$row) {
                $row['title'] = sprintf(
                    '%s%s',
                    str_repeat('-', $row['depth']),
                    $row['title']
                );
                $result[$row['id']] = $row['title'];
            }
            
            $default  = array();
            $withNull = (bool) $this->getOption('blank') ?: false;
            if ($withNull) {
                $default[0] = _a('Null');
            }
            $willAll  = (bool) $this->getOption('all') ?: false;
            if ($willAll) {
                $default['all'] = _a('All');
            }
            
            $this->valueOptions = $default + $result;
        }

        return $this->valueOptions;
    }
}
