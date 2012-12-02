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
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

namespace Module\System;

use Pi;
use Module\System\Form\LoginForm;

class Block
{
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

    public static function userbar()
    {
        return array(
            'identity'  => Pi::registry('user')->identity,
            'id'        => Pi::registry('user')->id,
            'name'      => Pi::registry('user')->name,
        );
    }

    public static function login()
    {
        if (!Pi::registry('user')->isGuest()) {
            return false;
        }
        $form = new LoginForm('login');
        $form->setAttribute('action', Pi::engine()->application()->getRouter()->assemble(array('module' => 'system', 'controller' => 'login', 'action' => 'process'), array('name' => 'user')));

        return array(
            'form'  => $form,
        );
    }
}
