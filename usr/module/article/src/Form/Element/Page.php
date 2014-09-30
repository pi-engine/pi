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
 * Page form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Page extends Select
{
    /**
     * Read all added categories from database
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
            $options = Pi::model('page', $module)
                ->getSelectOptions();
            $options = array_filter($options);
            $this->valueOptions = array('0' => _a('Null')) + $options;
        }

        return $this->valueOptions;
    }
}
