<?php
/**
 * Pi module block renderer
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
 * @package         Module\System
 */

namespace Module\System;

use Pi;
use Module\System\Form\LoginForm;

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
            'caption'   => sprintf(__('About %s'), Pi::config('sitename')),
            'items'     => array(
                __('Site name') => Pi::config('sitename'),
                __('Slogan')    => Pi::config('slogan'),
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
        if (Pi::registry('user')->isGuest()) {
            return false;
        }

        return array(
            'identity'  => Pi::registry('user')->identity,
            'id'        => Pi::registry('user')->id,
        );
    }

    /**
     * User bar
     *
     * @return array
     */
    public static function userbar()
    {
        return array(
            'identity'  => Pi::registry('user')->identity,
            'id'        => Pi::registry('user')->id,
            'name'      => Pi::registry('user')->name,
        );
    }

    /**
     * User login form block
     *
     * @return bool|array
     */
    public static function login()
    {
        if (!Pi::registry('user')->isGuest()) {
            return false;
        }
        $form = new LoginForm('login');
        $form->setAttribute('action', Pi::service('url')->assemble('user', array('module' => 'system', 'controller' => 'login', 'action' => 'process')));

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
        $featureApi = 'https://raw.github.com/pi-engine/pi/master/doc/README.html';
        return array(
            'api'   => $featureApi,
        );
    }
}
