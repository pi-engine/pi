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

$captchaMode = Pi::user()->config('register_captcha');
$captchaPublicKey = Pi::config('captcha_public_key');
$captchaPrivateKey = Pi::config('captcha_private_key');

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

$captchaElement = false;

if($captchaMode == 1){
    $captchaElement = array(
        'element' => array(
            'name'          => 'captcha',
            'type'          => 'captcha',
            'options'       => array(
                'label'     => _a('Please type the word.'),
                'separator'         => '<br />',
                'captcha_position'  => 'append',
            ),
            'attributes'    => array(
                'required' => true,
            ),
        ),
    );
} elseif($captchaMode == 2 && $captchaPublicKey && $captchaPrivateKey){
    $captchaElement = array(
        'element' => array(
            'name'          => 'captcha',
            'type'          => 'captcha',
            'options'       => array(
                'captcha' => new \LosReCaptcha\Captcha\ReCaptcha(array(
                        'site_key' => $captchaPublicKey,
                        'secret_key' => $captchaPrivateKey,
                    )
                ),
            ),
        ),
    );
}


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
