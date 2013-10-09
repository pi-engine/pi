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
 * Filter and validator for user level edit form
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class UserLevelEditFilter extends InputFilter
{
    /**
     * Initialize validator and filter 
     */
    public function __construct()
    {
        $this->add(array(
            'name'     => 'uid',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'Int',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'category',
            'required' => false,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name'     => 'level',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'Int',
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'id',
            'required' => false,
        ));
    }
}
