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
use Pi\Form\Form as BaseForm;

/**
 * User level edit form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class UserLevelEditForm extends BaseForm
{
    /**
     * Initialize form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'uid',
            'options'    => array(
                'label'       => __('User'),
            ),
            'attributes' => array(
                'description' => __('User account belong to article module'),
            ),
            'type'       => 'Module\Article\Form\Element\Account',
        ));
        
        $this->add(array(
            'name'       => 'category',
            'options'    => array(
                'label'       => __('Category'),
            ),
            'attributes' => array(
                'type'        => 'hidden',
                'description' => __('Categories allowed to manage'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'level',
            'options'    => array(
                'label'       => __('Level'),
            ),
            'attributes' => array(
                'description' => __('Category level'),
            ),
            'type'       => 'Module\Article\Form\Element\Level',
        ));
        
        $this->add(array(
            'name'       => 'security',
            'type'       => 'csrf',
        ));

        $this->add(array(
            'name'       => 'id',
            'attributes' => array(
                'type'        => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(                
                'value'       => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}
