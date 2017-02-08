<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    protected $config = array();

    /**
     * Constructor
     *
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config = array())
    {
        if (!$config) {
            $config = Pi::user()->config();
        }
        $this->config  = $config;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $config = $this->config;

        $captchaMode = $config['login_captcha'];
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

        // Get config data.
        $this->add(array(
            'name'          => 'identity',
            'type'          => 'Pi\Form\Element\LoginField',
            'options'       => array(
                'fields'    => $config['login_field'],
            ),
            'attributes' => array(
                'autocomplete' => in_array('email', $config['login_field']) ? 'email' : 'username',
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

        if ($captchaElement) {
            $this->add($captchaElement);
        }

        if (!empty($config['rememberme'])) {
            $this->add(array(
                'name'          => 'rememberme',
                'type'          => 'checkbox',
                'options'       => array(
                    'label' => __('Remember me'),
                ),
                'attributes'    => array(
                    'value'         => '1',
                    'description'   => __('Remember login status')
                )
            ));
        }

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $redirect = _get('redirect');
        if (!$redirect) {
            $routeMatch = Pi::engine()->application()->getRouteMatch();
            if ($routeMatch) {
                $module = $routeMatch->getParam('module');
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
