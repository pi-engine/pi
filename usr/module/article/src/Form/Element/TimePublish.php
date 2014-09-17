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
use Zend\Form\Element\Hidden;

/**
 * Time publish element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TimePublish extends Hidden
{
    /**
     * Custom element attributes
     * 
     * @var array 
     */
    protected $attributes = array(
        'type'  => 'Module\Article\Form\View\Helper\TimePublish',
    );
}
