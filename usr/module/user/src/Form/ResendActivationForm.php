<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $this->add(array(
            'name'          => 'email',
            'options'       => array(
                'label' => __('Email address'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'captcha',
            'type'          => 'captcha',
            'options'       => array(
                'label'     => __('Please type the word.'),
                'separator' => '<br />',
            )
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}