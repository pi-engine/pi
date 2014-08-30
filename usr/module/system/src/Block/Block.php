<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Block;

use Pi;
use Module\System\Form\LoginForm as LoginFormSystem;
use Module\User\Form\LoginForm as LoginFormUser;

/**
 * Block renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block
{
    /**
     * Site information block
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
     * Render types: js, dropdown, flat
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
            $params = array();
        }
        $result = array(
            'type'  => $type,
        );

        if ('js' == $type) {
            $user = array(
                'uid'       => 0,
                'logout'    => Pi::service('authentication')->getUrl('logout', $params),
                'login'     => Pi::service('authentication')->getUrl('login', $params),
                'register'  => Pi::service('user')->getUrl('register', $params),
            );
            $url = Pi::service('url')->assemble('default', array_replace($params, array(
                'module'        => 'system',
                'controller'    => 'index',
                'action'        => 'user',
            )));
            $result['callback'] = Pi::url($url, true);
        } elseif (!Pi::service('user')->hasIdentity()) {
            $user = array(
                'uid'       => 0,
                'login'     => Pi::service('authentication')->getUrl('login', $params),
                'register'  => Pi::service('user')->getUrl('register', $params),
            );
        } else {
            $uid = Pi::service('user')->getUser()->get('id');
            $name = Pi::service('user')->getUser()->get('name');
            $avatar = Pi::service('user')->getPersist('avatar-mini');
            if (!$avatar) {
                $avatar = Pi::service('user')->avatar($uid, 'mini');
                Pi::service('user')->setPersist('avatar-mini', $avatar);
            }
            $user = array(
                'uid'       => Pi::service('user')->getId(),
                'name'      => $name,
                'avatar'    => $avatar,
                'profile'   => Pi::service('user')->getUrl('profile', $params),
                'logout'    => Pi::service('authentication')->getUrl('logout', $params),
                'message'   => Pi::service('user')->message()->getUrl(),
            );
        }

        $result['user'] = $user;

        return $result;
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
        if (Pi::service('module')->isActive('user')) {
            $form = new LoginFormUser('login');
        } else {
            $form = new LoginFormSystem('login');
        }
        if (!empty($options['route'])) {
            $route = $options['route'];
            unset($options['route']);
        } else {
            $route = Pi::service('user')->getRoute();
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
