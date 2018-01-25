<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

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
        $this->add([
            'name'       => 'email',
            'options'    => [
                'label' => __('Email'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $captchaMode = Pi::user()->config('register_captcha');
        if ($captchaElement = Pi::service('form')->getReCaptcha($captchaMode)) {
            $this->add($captchaElement);
        }

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Find password'),
            ],
        ]);
    }
}