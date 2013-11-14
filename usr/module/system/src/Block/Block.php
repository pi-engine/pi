<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Block;

use Pi;
use Module\System\Form\LoginForm;

/**
 * Block renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block
{
    /**
     * Site infomation block
     *
     * @return array
     */
    public static function site()
    {
        return array(
            'caption'   => sprintf(_b('About %s'), Pi::config('sitename')),
            'items'     => array(
                _b('Site name') => Pi::config('sitename'),
                _b('Slogan')    => Pi::config('slogan'),
            ),
        );
    }

    /**
     * User link block
     *
     * @return bool|array
     */
    public static function user()
    {
        if (!Pi::service('user')->hasIdentity()) {
            return false;
        }

        return array(
            'identity'  => Pi::service('user')->getIdentity(),
            'id'        => Pi::service('user')->getId(),
        );
    }

    /**
     * User bar
     *
     * @param array $options
     *
     * @return array
     */
    public static function userbar($options = array())
    {
        if (!empty($options['type'])) {
            $type = $options['type'];
        } else {
            $type = '';
        }
        if (!empty($options['params'])) {
            $params = $options['params'];
        } else {
            $params = null;
        }
        if (Pi::service('user')->hasIdentity()) {
            $name = Pi::service('user')->getUser()->get('name');
            $user = array(
                'uid'       => Pi::service('user')->getId(),
                'name'      => $name,
                'profile'   => Pi::service('user')->getUrl('profile', $params),
                'logout'    => Pi::service('authentication')->getUrl('logout', $params),
            );
            $message = array(
                'url'       => Pi::service('user')->message()->getUrl(),
            );
        } else {
            $user = array(
                'uid'       => 0,
                'login'     => Pi::service('authentication')->getUrl('login', $params),
                'register'  => Pi::service('user')->getUrl('register', $params),
            );
            $message = array();
        }

        return array(
            'user'      => $user,
            'message'   => $message,
            'type'      => $type,
        );
    }

    /**
     * User login form block
     *
     * @param array $options
     *
     * @return bool|array
     */
    public static function login($options = array())
    {
        if (Pi::service('user')->hasIdentity()) {
            return false;
        }
        $form = new LoginForm('login');
        if (!empty($options['route'])) {
            $route = $options['route'];
            unset($options['route']);
        } else {
            $route = 'sysuser';
        }
        if (!empty($options['action'])) {
            $action = $options['action'];
            unset($options['action']);
        } else {
            $action = Pi::service('url')->assemble(
                $route,
                array(
                    'module'        => 'system',
                    'controller'    => 'login',
                    'action'        => 'process',
                )
            );
        }
        if ($options) {
            $form->setData($options);
        }
        $form->setAttribute('action', $action);

        return array(
            'form'  => $form,
        );
    }

    /**
     * Get Pi Engine feature API
     *
     * @return string
     */
    public static function pi()
    {
        $featureApi =
            'https://raw.github.com/pi-engine/pi/master/doc/README.html';
        return array(
            'api'   => $featureApi,
        );
    }
}
