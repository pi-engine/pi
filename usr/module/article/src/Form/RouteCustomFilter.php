<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Validate and filer form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class RouteCustomFilter extends InputFilter
{
    /**
     * Initializing validator and filter 
     */
    public function __construct()
    {
        $this->add(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'section',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'priority',
            'required' => false,
            'filters'  => array(
                array(
                    'name' => 'Int',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'type',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'structure_delimiter',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'param_delimiter',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'key_value_delimiter',
            'required' => false,
        ));
        
        $this->add(array(
            'name'     => 'route',
            'required' => false,
        ));

        $this->add(array(
            'name'     => 'module',
            'required' => false,
        ));
        
        $this->add(array(
            'name'     => 'controller',
            'required' => false,
        ));
        
        $this->add(array(
            'name'     => 'action',
            'required' => false,
        ));
    }
}
