<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
            'type'  => 'text',
        ));

        $this->add(array(
            'name'          => 'textarea',
            'options'       => array(
                'label' => __('Textarea'),
            ),
            'type'  => 'textarea',
            /*
            'attributes'    => array(
                'rows'  => 3,   // default value
            ),
            */
            'required' => true,
        ));

        $this->add(array(
            'name'          => 'select',
            'options'       => array(
                'label' => __('Select'),
                'value_options' => array(
                     '0' => 'Female',
                     '1' => 'Male',
                 ),
            ),
            'type'  => 'select',
        ));

        $this->add(array(
            'name'          => 'upload_demo',
            'options'       => array(
                'label' => __('File'),
            ),
            'type'  => 'file',
        ));

        $this->add(array(
            'name'          => 'disabled',
            'options'       => array(
                'label' => __('Disabled'),
            ),
            'type'  => 'text',
            'attributes'    => array(
                'disabled' => 'disabled'
            )
        ));

        $this->add(array(
            'name'          => 'custom_cls',
            'options'       => array(
                'label' => __('Custom class'),
            ),
            'type'  => 'textarea',
            'attributes'    => array(
                'rows'  => 5, // default as 3
                'class' => 'pi-test'
            ),
        ));

        $this->add(array(
            'name'          => 'custom_width',
            'options'       => array(
                'label' => __('Custom width'),
            ),
            'type'  => 'text',
            'attributes'    => array(
                'data-width'  => '3',
            ),
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
            'attributes'    => array(
                'class' => 'pi-test'
            ),
        ));

        $this->add(array(
            'name'          => 'radios2',
            'options'       => array(
                'label' => __('Radios'),
                'value_options' => array(
                     '0' => 'Female',
                     '1' => 'Male',
                 ),
                'label_attributes' => array(
                    'class' => 'radio-inline'
                )
            ),
            'type'  => 'radio',
            'attributes'    => array(
                'class' => 'pi-test'
            ),
        ));

        $this->add(array(
            'name'          => 'checkbox',
            'options'       => array(
                'label' => __('Checkbox'),
            ),
            'type'  => 'checkbox',
        ));

        $this->add(array(
            'name'          => 'checkboxs',
            'options'       => array(
                'label' => __('Checkboxs'),
                'value_options' => array(
                     '0' => 'Checkbox1',
                     '1' => 'Checkbox2',
                     '2' => 'Checkbox3',
                     '3' => 'Checkbox4',
                     '4' => 'Checkbox5',
                     '5' => 'Checkbox6',
                ),
                'label_attributes' => array(
                    'class' => 'checkbox-inline'
                )
            ),
            'type'          => 'multi_checkbox',
        ));

        $this->add(array(
            'name'          => 'checkboxs2',
            'options'       => array(
                'label' => __('Checkboxs'),
                'value_options' => array(
                     '0' => 'Checkbox1',
                     '1' => 'Checkbox2',
                ),
            ),
            'type'          => 'multi_checkbox',
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
                'class' => 'btn btn-primary'
            )
        ));
    }
}
