<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Filter;

use Pi;
use Zend\InputFilter\Input;

/**
 * Time publish filter class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TimePublish extends Input
{
    /**
     * Change time date format into timestamp
     * 
     * @return string
     */
    public function getValue()
    {
        $value = parent::getValue();
        
        return $value ? strtotime($value) : 0;
    }
}
