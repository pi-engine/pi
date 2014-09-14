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
use Zend\Form\Element\Text;

/**
 * Subtitle element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Subtitle extends Text
{
    /**
     * Constructor, set element attributes
     * 
     * @param string $name
     * @param array  $options
     */
    public function __construct($name = null, $options = array())
    {
        $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
        $length = Pi::config('max_subtitle_length', $module);
        $this->setAttributes(array(
            'id'        => 'subtitle',
            'data-size' => $length,
        ));
        
        parent::__construct($name, $options);
    }
}
