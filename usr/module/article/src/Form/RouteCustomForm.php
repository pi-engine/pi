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
 * Custom route form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class RouteCustomForm extends BaseForm
{
    /**
     * Initialize form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'name',
            'options'    => array(
                'label'     => __('Route Name'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'section',
            'options'    => array(
                'label'     => __('Section'),
            ),
            'attributes' => array(
                'options'   => array(
                    'front'     => __('Front'),
                ),
            ),
            'type'       => 'select',
        ));
        
        $this->add(array(
            'name'       => 'priority',
            'options'    => array(
                'label'     => __('Priority'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'type',
            'options'    => array(
                'label'     => __('Class'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'structure_delimiter',
            'options'    => array(
                'label'     => __('Structure Delimiter'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'param_delimiter',
            'options'    => array(
                'label'     => __('Param Delimiter'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'key_value_delimiter',
            'options'    => array(
                'label'     => __('Key Value Delimiter'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'route',
            'options'    => array(
                'label'     => __('URL Prefix'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'module',
            'options'    => array(
                'label'     => __('Module'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'controller',
            'options'    => array(
                'label'     => __('Controller'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));
        
        $this->add(array(
            'name'       => 'action',
            'options'    => array(
                'label'     => __('Action'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(                
                'value' => __('Save'),
            ),
            'type'  => 'submit',
        ));
    }
}
