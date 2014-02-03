<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * User login form
 */
class LoginForm extends BaseForm
{
    public function init()
    {
        $config = Pi::service('module')->config('', 'user');

        // Get config data.
        $this->add(array(
            'name'          => 'identity',
            'type'          => 'Module\User\Form\Element\LoginField',
            'options'       => array(
                'fields'    => $config['login_field'],
            ),
        ));

        $this->add(array(
            'name'          => 'credential',
            'options'       => array(
                'label' => __('Password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ));

        if ($config['login_captcha']) {
            $this->add(array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'label'     => __('Please type the word.'),
                    'separator'         => '<br />',
                )
            ));
        }

        if ($config['rememberme']) {
            $this->add(array(
                'name'          => 'rememberme',
                'type'          => 'checkbox',
                'options'       => array(
                    'label' => __('Remember me'),
                ),
                'attributes'    => array(
                    'value'         => '1',
                    'description'   => __('Remember me')
                )
            ));
        }

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $redirect = _get('redirect') ?: Pi::service('url')->getRequestUri();
        $redirect = $redirect ? rawurlencode($redirect) : '';
        $this->add(array(
            'name'  => 'redirect',
            'type'  => 'hidden',
            'attributes'    => array(
                'value' => $redirect,
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(
                'type'  => 'submit',
                'value' => __('Login'),
                'class' => 'btn btn-primary',
            )
        ));
    }
}
