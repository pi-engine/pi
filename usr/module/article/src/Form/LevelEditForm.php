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
 * Level edit form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class LevelEditForm extends BaseForm
{
    protected $resources = array();
    
    /**
     * Initialize parameters
     * 
     * @param string  $name
     * @param array   $options 
     */
    public function __construct($name, $options)
    {
        $this->resources = isset($options['resources']) 
            ? $options['resources'] : array();
        
        parent::__construct($name);
    }

    /**
     * Initialize form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'name',
            'options'    => array(
                'label'       => __('Name'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('The unique identifier of level.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'title',
            'options'    => array(
                'label'       => __('Title'),
            ),
            'attributes' => array(
                'type'        => 'text',
                'description' => __('Will be displayed on your website.'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'description',
            'options'    => array(
                'label'       => __('Description'),
            ),
            'attributes' => array(
                'type'        => 'textarea',
                'description' => __('Display in the website depends on theme.'),
            ),
        ));
        
        foreach ($this->resources as $key => $res) {
            foreach ($res as $key => $resource) {
                $this->add(array(
                    'name'        => $key,
                    'attributes'  => array(
                        'description' => ucfirst(str_replace('-', ' ', $resource)),
                    ),
                    'options'     => array(
                        'label'       => '',
                    ),
                    'type'        => 'checkbox',
                ));
            }
        }
        
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
