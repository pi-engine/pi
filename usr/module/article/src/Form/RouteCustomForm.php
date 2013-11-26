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
                'label'       => __('Route Name'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Route unique name, only letter, _ and - required'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'priority',
            'options'    => array(
                'label'       => __('Priority'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Route priority, we recommend you to set a number more that 100'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'type',
            'options'    => array(
                'label'       => __('Class'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Namespace of class which use to match and assemble url'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'structure_delimiter',
            'options'    => array(
                'label'       => __('Structure Delimiter'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Symbol use to delimit module, controller, action, etc.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'param_delimiter',
            'options'    => array(
                'label'       => __('Param Delimiter'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Symbol use to delimit different parameter groups'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'key_value_delimiter',
            'options'    => array(
                'label'       => __('Key Value Delimiter'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Symbol use to delimit parameter key and its value'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'route',
            'options'    => array(
                'label'       => __('URL Prefix'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Route prefix, it will help to match route more quickly and correctly'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'controller',
            'options'    => array(
                'label'       => __('Controller'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Default controller, not very important'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'action',
            'options'    => array(
                'label'       => __('Action'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Default action, not very important'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'section',
            'attributes' => array(
                'type'      => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name'       => 'module',
            'attributes' => array(
                'type'      => 'hidden',
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
