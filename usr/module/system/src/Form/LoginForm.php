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
 * Login form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginForm extends BaseForm
{
    protected $config = [];

    /**
     * Constructor
     *
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config = [])
    {
        if (!$config) {
            $config = Pi::user()->config();
        }
        $this->config = $config;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $config = $this->config;

        // Get config data.
        $this->add([
            'name'       => 'identity',
            'type'       => 'Pi\Form\Element\LoginField',
            'options'    => [
                'fields' => $config['login_field'],
            ],
            'attributes' => [
                'autocomplete' => in_array('email', $config['login_field']) ? 'email' : 'username',
            ],
        ]);

        $this->add([
            'name'       => 'credential',
            'options'    => [
                'label' => __('Password'),
            ],
            'attributes' => [
                'type' => 'password',
            ],
        ]);

        $captchaMode = $config['login_captcha'];
        if ($captchaElement = Pi::service('form')->getReCaptcha($captchaMode)) {
            $this->add($captchaElement);
        }

        if (!empty($config['rememberme'])) {
            $this->add([
                'name'       => 'rememberme',
                'type'       => 'checkbox',
                'options'    => [
//                    'label' => __('Remember me'),
                ],
                'attributes' => [
                    'value'       => '1',
                    'description' => __('Remember login status'),
                ],
            ]);
        }

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $redirect = _get('redirect');
        if (!$redirect) {
            $routeMatch = Pi::engine()->application()->getRouteMatch();
            if ($routeMatch) {
                $module     = $routeMatch->getParam('module');
                $controller = $routeMatch->getParam('controller');
                if (('user' == $module || 'system' == $module)
                    && ('login' == $controller || 'register' == $controller)
                ) {
                } else {
                    $redirect = Pi::service('url')->getRequestUri();
                }
            }
        }
        $redirect = $redirect ? rawurlencode($redirect) : '';
        $this->add([
            'name'       => 'redirect',
            'type'       => 'hidden',
            'attributes' => [
                'value' => $redirect,
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'type'  => 'submit',
                'value' => __('Login'),
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}
