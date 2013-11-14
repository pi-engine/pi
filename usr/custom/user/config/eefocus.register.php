<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User register form config custom for eefoucs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

return array(
    // Use user module field
    'email',
    'identity',
    'name',
    'credential',

    // Custom field
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

    'terms_of_service' => array(
        'element' => array(
            'name' => 'terms_of_service',
            'type' => 'checkbox',
            'options' => array(
                'label' => __(''),
            )
        ),
        'filter' => array(
            'name' => 'terms_of_service',
            'required' => true,
        ),
    ),
);
