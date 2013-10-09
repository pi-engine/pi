<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Level element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Level extends Select
{
    /**
     * Read added level from database
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
            $model  = Pi::model('level', $module);
            $rowset = $model->select(array('active' => 1));
            $levels = array();
            foreach ($rowset as $row) {
                $levels[$row->id] = $row->title;
            }
            $levels = empty($levels) ? array(0 => __('Null')) : $levels;
            $this->valueOptions = $levels;
        }

        return $this->valueOptions;
    }
}
