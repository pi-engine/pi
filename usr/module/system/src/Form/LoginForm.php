<?php
/**
 * Login form
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\System\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class LoginForm extends BaseForm
{
    /*
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new LoginFilter;
        }
        return $this->filter;
    }
    */

    public function init()
    {
        $config = Pi::service('registry')->config->read('', 'user');

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

        if ($config['rememberme']) {
            $this->add(array(
                'name'          => 'rememberme',
                'type'          => 'checkbox',
                'options'       => array(
                    'label' => __('Remember me'),
                ),
                'attributes'    => array(
                    'value'         => '1',
                    'description'   => __('Remember login status for 14 days.')
                )
            ));
        }

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

        $redirect = Pi::engine()->application()->getRequest()->getServer('HTTP_REFERER') ?: Pi::engine()->application()->getRequest()->getRequestUri();
        $this->add(array(
            'name'  => 'redirect',
            'type'  => 'hidden',
            'attributes'    => array(
                'value' => urlencode($redirect),
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
