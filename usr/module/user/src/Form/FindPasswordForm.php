<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $captchaMode = Pi::user()->config('register_captcha');
        $captchaPublicKey = Pi::config('captcha_public_key');
        $captchaPrivateKey = Pi::config('captcha_private_key');

        $captchaElement = false;

        if($captchaMode == 1){
            $captchaElement = array(
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
            );
        } elseif($captchaMode == 2 && $captchaPublicKey && $captchaPrivateKey){
            $captchaElement = array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'captcha' => new \LosReCaptcha\Captcha\ReCaptcha(array(
                            'site_key' => $captchaPublicKey,
                            'secret_key' => $captchaPrivateKey,
                        )
                    ),
                ),
            );
        }

        $this->add(array(
            'name'       => 'email',
            'options'    => array(
                'label' => __('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add($captchaElement);

        $this->add(array(
            'name' => 'security',
            'type' => 'csrf',
        ));

        $this->add(array(
            'name'       => 'submit',
            'type'  => 'submit',
            'attributes' => array(
                'value' => __('Find password'),
            ),
        ));
    }
}