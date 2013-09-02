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
 * Login form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class LoginForm extends BaseForm
{
    public function init()
    {
        // Get config data.
        $config = Pi::service('registry')->config->read('user', 'general');

        $this->add(array(
            'name'          => 'identity',
            'options'       => array(
                'label' => __('Username'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
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

        $this->add(array(
            'name'          => 'rememberme',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Remember me'),
            ),
            'attributes'    => array(
                'value'         => '1',
                'description'   => __('Keep me logged in.')
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

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $request = Pi::engine()->application()->getRequest();
        $redirect = $request->getQuery('redirect');
        if (null === $redirect) {
            $redirect = $request->getServer('HTTP_REFERER') ?: $request->getRequestUri();
        }
        $redirect = $redirect ? urlencode($redirect) : '';
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
                'class' => 'btn',
            )
        ));
    }
}
