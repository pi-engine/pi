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
 * Register form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RegisterForm extends BaseForm
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $config = Pi::user()->config();

        $this->add(array(
            'name'          => 'identity',
            'options'       => array(
                'label' => __('User account'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'required' => true,
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Display name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'required' => true,
            )
        ));

        $this->add(array(
            'name'          => 'email',
            'options'       => array(
                'label' => __('Email address'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'required' => true,
            )
        ));

        $this->add(array(
            'name'          => 'credential',
            'options'       => array(
                'label' => __('Password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
                'required' => true,
            )
        ));

        $this->add(array(
            'name'          => 'credential-confirm',
            'options'       => array(
                'label' => __('Confirm password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
                'required' => true,
            )
        ));

        if ($config['register_captcha']) {
            $this->add(array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'label'     => __('Please type the word.'),
                    'separator'         => '<br />',
                    'captcha_position'  => 'append',
                ),
                'attributes'    => array(
                    'required' => true,
                )
            ));
        }

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
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
