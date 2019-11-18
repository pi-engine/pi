<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt BSD 3-Clause License
*/

/**
* User register form config
*/

$captchaEnable = Pi::user()->config('register_captcha');
$termEnable = Pi::user()->config('register_term');
$termUrl = Pi::user()->config('register_term_url');

if ($termEnable && !empty($termUrl)) {
    $termEnable = true;
    $term = sprintf('<a href="%s" target="_blank">%s</a>', $termUrl, __('Terms & Conditions'));
    $term = sprintf(__('Accept %s'), $term);
} else {
    $termEnable = false;
    $term = '';
}

return array(
    // Use user module field
    'email',
    'first_name',
    'last_name',
    'credential',

    // Custom field
    'credential-confirm' => array(
        'element' => array(
            //'name'          => 'credential-confirm',
            'options'       => array(
            ),
            'attributes'    => array(
                'type'  => 'password',
                'required' => true,
                'placeholder'     => _a('Confirm credential.'),

            ),
        ),

        'filter' => array(
            //'name'          => 'credential-confirm',
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

    'captcha' => !$captchaEnable ? false : array(
        'element' => array(
            //'name'          => 'captcha',
            'type'          => 'captcha',
            'options'       => array(
                'separator'         => '<br />',
                'captcha_position'  => 'append',
            ),
            'attributes'    => array(
                'required' => false,
                'placeholder'     => _a('Please type the word.'),

            ),
        ),
    ),

    'term'  => !$termEnable ? false : array(
        'element' => array(
            'name'          => 'term',
            'type'          => 'checkbox',
            'options'       => array(
                'label'     => '',
            ),
            'attributes' => array(
                'description' => $term,
                'required' => true,
            )
        ),
    ),

    'register_source'   => array(
        'element' => array(
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => isset($_GET['source']) ? $_GET['source'] : '',
            ),
        ),

        'filter' => array(
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ),
    ),
);