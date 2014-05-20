<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Module\Article\Controller\Admin\SetupController;

/**
 * Custom draft form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftCustomForm extends BaseForm
{
    /**
     * Form elements in draft edit page
     * @var array 
     */
    protected $items = array();
    
    /**
     * Saved custom elements
     * @var array 
     */
    protected $custom = array();
    
    public function __construct($name, $options = array())
    {
        $this->items = isset($options['elements']) 
            ? $options['elements'] : array();
        $this->custom = isset($options['custom'])
            ? $options['custom'] : array();
        parent::__construct($name);
    }
    
    /**
     * Initialize form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'mode',
            'options'    => array(
                'label'    => __('Form Mode'),
            ),
            'attributes' => array(
                'value'    => SetupController::FORM_MODE_EXTENDED,
                'options'  => array(
                    SetupController::FORM_MODE_NORMAL   => __('Normal'),
                    SetupController::FORM_MODE_EXTENDED => __('Extended'),
                    SetupController::FORM_MODE_CUSTOM   => __('Custom'),
                ),
            ),
            'type'       => 'radio',
        ));
        
        foreach ($this->items as $name => $title) {
            $this->add(array(
                'name'          => $name,
                'options'       => array(
                    'label'     => $title,
                ),
                'attributes'    => array(
                    'value'     => in_array($name, $this->custom) ? 1 : 0,
                ),
                'type'          => 'checkbox',
            ));
        }

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(                
                'value' => __('Submit'),
            ),
            'type'  => 'submit',
        ));
    }
}
