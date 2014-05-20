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
class CategoryWithRoot extends Select
{
    /**
     * Reading all added categories from database
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $module = $this->getOption('module')
                ?: Pi::service('module')->current();
            $this->valueOptions = Pi::model('category', $module)
                ->getSelectOptions(true);
            if ('system' == Pi::service('module')->current()) {
                $this->valueOptions = array(0 => __('Null')) + $this->valueOptions;
            }
        }

        return $this->valueOptions;
    }
}
