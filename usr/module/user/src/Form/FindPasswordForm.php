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
        $this->add(array(
            'name'       => 'email',
            'options'    => array(
                'label' => __('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $captchaMode = Pi::user()->config('register_captcha');
        if($captchaElement = Pi::service('form')->getReCaptcha($captchaMode)){
            $this->add($captchaElement);
        }

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