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
 * Category form class for extending category selection
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Category extends Select
{
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
            $this->valueOptions = Pi::model('category', $module)
                ->getSelectOptions();
        }

        return $this->valueOptions;
    }
}
