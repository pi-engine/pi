<?php
/**
 * Register form config
 *
 */

return array(
    'email'      => array(
        'element' => Pi::api('user', 'form')->getElement('email'),
        'filter'  => Pi::api('user', 'form')->getFilter('email') + array('required' => true),
    ),

    'identity'   => array(
        'element' => Pi::api('user', 'form')->getElement('identity'),
        'filter'  => Pi::api('user', 'form')->getFilter('identity') + array('required' => true),
    ),

    'name'       => array(
        'element' => Pi::api('user', 'form')->getElement('name'),
        'filter'  => Pi::api('user', 'form')->getFilter('name') + array('required' => true),
    ),

    'credential' => array(
        'element' => Pi::api('user', 'form')->getElement('credential'),
        'filter'  => Pi::api('user', 'form')->getFilter('credential') + array('required' => true),
    ),

    'credential-confirm' => array(
        'element' => array(
            'name'          => 'credential-confirm',
            'options'       => array(
                'label' => __('Confirm credential'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ),

        'filter' => array(
            'name'          => 'credential-confirm',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'Identical',
                    'options'   => array(
                        'token'     => 'credential',
                        'strict'    => true,
                    ),
                ),
            ),
        ),
    ),

    'captcha' => array(
        'element' => array(
            'name'          => 'captcha',
            'type'          => 'captcha',
            'options'       => array(
                'label'     => __('Please type the word.'),
                'separator'         => '<br />',
                'captcha_position'  => 'append',
            )
        ),
        'filter'  => array(),
    ),
);