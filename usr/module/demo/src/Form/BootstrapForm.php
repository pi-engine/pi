<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class BootstrapForm extends BaseForm
{
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new RouteFilter;
        }

        return $this->filter;
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'input',
            'options'       => array(
                'label' => __('Input'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'textarea',
            'options'       => array(
                'label' => __('Textarea'),
            ),
            'type'  => 'textarea',
            'attributes'    => array(
                'rows'  => '3'
            )
        ));

        $this->add(array(
            'name'          => 'radios',
            'options'       => array(
                'label' => __('Radios'),
                'value_options' => array(
                     '0' => 'Female',
                     '1' => 'Male',
                 ),

            ),
            'type'  => 'radio',
        ));

        $this->add(array(
            'name'          => 'checkbox',
            'options'       => array(
                'label' => __('Checkbox'),
            ),
            'type'  => 'checkbox',
        ));

        $this->add(array(
            'name'          => 'checkbox',
            'options'       => array(
                'label' => __('Checkbox'),
                'value_options' => array(
                     '0' => 'Checkbox1',
                     '1' => 'Checkbox2',
                ),  
            ),
            'type'          => 'multiCheckbox',
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 0,
            )
        ));

        $this->add(array(
            'name'          => 'module',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => '',
            )
        ));

        $this->add(array(
            'name'          => 'section',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 'front',
            )
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
