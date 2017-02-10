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

$captchaMode = Pi::user()->config('register_captcha');
$captchaElement = array(
    'element' => Pi::service('form')->getReCaptcha($captchaMode)
);

return array(
    // Use user module field
    'email',
    'name',
    'identity',
    'credential',

    // Custom field
    'credential-confirm' => array(
        'element' => array(
            //'name'          => 'credential-confirm',
            'options'       => array(
                'label' => __('Confirm credential'),
            ),
            'attributes'    => array(
                'type'  => 'password',
                'required' => true,
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

    'captcha' => $captchaElement,

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
);
