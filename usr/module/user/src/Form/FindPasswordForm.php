<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of find password
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class FindPasswordForm extends BaseForm
{
    public function init()
    {
        // Check is mobile
        if (Pi::user()->config('is_mobile')) {
            $this->add(
                [
                    'name'       => 'identity',
                    'options'    => [
                        'label' => __('Mobile number'),
                    ],
                    'attributes' => [
                        'type'     => 'text',
                        'required' => true,
                    ],
                ]
            );
        } else {
            $this->add(
                [
                    'name'       => 'email',
                    'options'    => [
                        'label' => __('Email'),
                    ],
                    'attributes' => [
                        'type'     => 'text',
                        'required' => true,
                    ],
                ]
            );
        }

        $captchaMode = Pi::user()->config('register_captcha');
        if ($captchaElement = Pi::service('form')->getReCaptcha($captchaMode)) {
            $this->add($captchaElement);
        }

        $this->add(
            [
                'name' => 'security',
                'type' => 'csrf',
            ]
        );
        $this->add(
            [
                'name' => 'redirect',
                'type' => 'hidden',
            ]
        );
        $this->add(
            [
                'name'       => 'submit-button',
                'type'       => 'submit',
                'options'    => [
                    'label' => __('Send reset link'),

                ],
                'attributes' => [
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
