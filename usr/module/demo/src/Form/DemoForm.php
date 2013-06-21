<?php
namespace Module\Demo\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Module\Demo\Form\DemoFilter;

class DemoForm extends BaseForm
{
	public function init()
	{
        $this->add(array(
            'name' => 'username',
            'attributes'    => array(
                'type'  => 'text',               
            ),
            'options' => array(
                'label' => __('Username'),
            ),
        ));
        $this->add(array(
            'name' => 'textarea',
            'attributes'    => array(
                'type'  => 'textarea',
                'rows' => 5               
            ),
            'options' => array(
                'label' => __('textarea'),
            )
        ));
        $this->add(array(
            'name' => 'checkbox',
            'type' => 'checkbox',
            'attributes' => array(
                'checked' => 0,
                'description' => 'checkbox'
            ),
            'options' => array(
                'label' => __('checkbox'),
            ),
        ));
        $this->add(array(
            'name' => 'multiple',
            'type' => 'multiCheckbox',
            'options' => array(
                 'label' => 'What do you like ?',
                 'value_options' => array(
                     'Apple' => 'Apple',
                     'Orange' => 'Orange',
                     'Lemon' => 'Lemon',
                 ),
             )
        ));
        $this->add(array(
            'name' => 'sex',
            'type' => 'radio',
            'options' => array(
                 'label' => 'sex',
                 'value_options' => array(
                     'male' => 'male',
                     'female' => 'female'
                 ),
             ),
            'attributes' => array(
                'value' => 'male'
            )
        ));
        $this->add(array(
            'name' => 'select',
            'type' => 'select',
            'options' => array(
                 'label' => 'select',
                 'value_options' => array(
                     '' => 'please select',
                     '1' => 'select2',
                     '2' => 'select3'
                 ),
             ),
            'attributes' => array(
                'class' => 'input-medium'
            )
        ));
        $this->add(array(
            'name' => 'multipselect',
            'type' => 'select',
            'options' => array(
                 'label' => 'select group',
                 'empty_option' => 'please select',
                 'value_options' => array(
                     'european' => array(
                         'label' => 'European languages',
                         'options' => array(
                             '0' => 'French',
                             '1' => 'Italian',
                         ),
                     ),
                     'asian' => array(
                         'label' => 'Asian languages',
                         'options' => array(
                             '2' => 'Japanese',
                             '3' => 'Chinese',
                         ),
                     ),
                 ),
             )
        ));
        $this->add(array(
            'name' => 'captcha',
            'type' => 'captcha',
            'options' => array(
                'label' => __('Please verify you are human'),
            )
        ));
        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(
                'type'  => 'submit',
                'value' => __('Submit'),
                'class' => 'btn btn-primary'
            )
        ));
        //set form validate
        $this->setInputFilter(new DemoFilter);
	}
}