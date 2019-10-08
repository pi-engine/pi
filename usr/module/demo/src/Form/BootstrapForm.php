<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Form;

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
        $this->add([
            'name'    => 'input',
            'options' => [
                'label' => __('Input'),
            ],
            'type'    => 'text',
        ]);

        $this->add([
            'name'     => 'textarea',
            'options'  => [
                'label' => __('Textarea'),
            ],
            'type'     => 'textarea',
            /*
            'attributes'    => array(
                'rows'  => 3,   // default value
            ),
            */
            'required' => true,
        ]);

        $this->add([
            'name'    => 'select',
            'options' => [
                'label'         => __('Select'),
                'value_options' => [
                    '0' => 'Female',
                    '1' => 'Male',
                ],
            ],
            'type'    => 'select',
        ]);

        $this->add([
            'name'    => 'upload_demo',
            'options' => [
                'label' => __('File'),
            ],
            'type'    => 'file',
        ]);

        $this->add([
            'name'       => 'disabled',
            'options'    => [
                'label' => __('Disabled'),
            ],
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        $this->add([
            'name'       => 'custom_cls',
            'options'    => [
                'label' => __('Custom class'),
            ],
            'type'       => 'textarea',
            'attributes' => [
                'rows'  => 5, // default as 3
                'class' => 'pi-test',
            ],
        ]);

        $this->add([
            'name'       => 'custom_width',
            'options'    => [
                'label' => __('Custom width'),
            ],
            'type'       => 'text',
            'attributes' => [
                'data-width' => '3',
            ],
        ]);

        $this->add([
            'name'       => 'radios',
            'options'    => [
                'label'         => __('Radios'),
                'value_options' => [
                    '0' => 'Female',
                    '1' => 'Male',
                ],

            ],
            'type'       => 'radio',
            'attributes' => [
                'class' => 'pi-test',
            ],
        ]);

        $this->add([
            'name'       => 'radios2',
            'options'    => [
                'label'            => __('Radios'),
                'value_options'    => [
                    '0' => 'Female',
                    '1' => 'Male',
                ],
                'label_attributes' => [
                    'class' => 'form-check-label',
                ],
            ],
            'type'       => 'radio',
            'attributes' => [
                'class' => 'pi-test',
            ],
        ]);

        $this->add([
            'name'    => 'checkbox',
            'options' => [
                'label' => __('Checkbox'),
            ],
            'type'    => 'checkbox',
        ]);

        $this->add([
            'name'    => 'checkboxs',
            'options' => [
                'label'            => __('Checkboxs'),
                'value_options'    => [
                    '0' => 'Checkbox1',
                    '1' => 'Checkbox2',
                    '2' => 'Checkbox3',
                    '3' => 'Checkbox4',
                    '4' => 'Checkbox5',
                    '5' => 'Checkbox6',
                ],
                'label_attributes' => [
                    'class' => 'checkbox-inline',
                ],
            ],
            'type'    => 'multi_checkbox',
        ]);

        $this->add([
            'name'    => 'checkboxs2',
            'options' => [
                'label'         => __('Checkboxs'),
                'value_options' => [
                    '0' => 'Checkbox1',
                    '1' => 'Checkbox2',
                ],
            ],
            'type'    => 'multi_checkbox',
        ]);


        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type'  => 'hidden',
                'value' => 0,
            ],
        ]);

        $this->add([
            'name'       => 'module',
            'attributes' => [
                'type'  => 'hidden',
                'value' => '',
            ],
        ]);

        $this->add([
            'name'       => 'section',
            'attributes' => [
                'type'  => 'hidden',
                'value' => 'front',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}
