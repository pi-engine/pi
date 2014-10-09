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
use Zend\Form\Element\MultiCheckbox;

/**
 * Cluster form class for extending cluster selection
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MultiCluster extends MultiCheckbox
{
    /**
     * Table name
     * @var string 
     */
    protected $table = 'cluster';
    
    /**
     * Read all added clusters from database without root node
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
            $method   = sprintf('get%sList', ucfirst($this->table));
            $rowset   = Pi::api('api', $module)->$method(
                array('active' => 1),
                array('id', 'title', 'depth')
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
            
            $this->valueOptions = $result;
        }

        return $this->valueOptions;
    }
}
