<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * User register form config
 */

$termEnable = Pi::user()->config('register_term');
$termUrl    = Pi::user()->config('register_term_url');

if ($termEnable && !empty($termUrl)) {
    $termEnable = true;
    $term       = sprintf('<a href="%s" target="_blank">%s</a>', $termUrl, __('Terms & Conditions'));
    $term       = sprintf(__('Accept the %s'), $term);
} else {
    $termEnable = false;
    $term       = '';
}

$captchaMode    = Pi::user()->config('register_captcha');
$captchaElement = [
    'element' => Pi::service('form')->getReCaptcha($captchaMode),
];

$form = [
    // Use user module field
    'email',
    'name',
    'identity',
    'credential',

    // Custom field
    'credential-confirm' => [
        'element' => [
            //'name'          => 'credential-confirm',
            'options'    => [
                'label' => __('Confirm credential'),
            ],
            'attributes' => [
                'type'     => 'password',
                'required' => true,
            ],
        ],

        'filter' => [
            //'name'          => 'credential-confirm',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'Identical',
                    'options' => [
                        'token'  => 'credential',
                        'strict' => true,
                    ],
                ],
            ],
        ],
    ],

    'captcha' => $captchaElement,

    'term' => !$termEnable ? false : [
        'element' => [
            'name'       => 'term',
            'type'       => 'checkbox',
            'options'    => [
                'label' => '',
            ],
            'attributes' => [
                'description' => $term,
                'required'    => true,
            ],
        ],
    ],
];

if ($captchaMode == 0) {
    unset($form['captcha']);
}

return $form;