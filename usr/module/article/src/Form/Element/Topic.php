<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Topic form element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Topic extends Select
{
    /**
     * Read added topic from database
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
            $model  = Pi::model('topic', $module);
            $rowset = $model->select(array());
            $topics = array();
            foreach ($rowset as $row) {
                $topics[$row->id] = $row->title;
            }
            $this->valueOptions = array(0 => __('Null')) + $topics;
        }

        return $this->valueOptions;
    }
}
