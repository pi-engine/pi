<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of resent activate mail
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class ResendActivationForm extends BaseForm
{
    public function init()
    {
        $this->add([
            'name'       => 'email',
            'options'    => [
                'label' => __('Email address'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'    => 'captcha',
            'type'    => 'captcha',
            'options' => [
                'label'     => __('Please type the word.'),
                'separator' => '<br />',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
