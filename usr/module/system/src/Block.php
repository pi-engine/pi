<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System;

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
        if (!Pi::service('user')->hasIdentity()) {
            return false;
        }

        return array(
            'identity'  => Pi::service('user')->getUser()->identity,
            'id'        => Pi::service('user')->getUser()->id,
        );
    }

    /**
     * User bar
     *
     * @param array $options
     * @return array
     */
    public static function userbar($options = array())
    {
        return array(
            'identity'  => Pi::service('user')->getUser()->identity,
            'id'        => Pi::service('user')->getUser()->id,
            'name'      => Pi::service('user')->getUser()->name,
            'type'      => isset($options['type']) ? $options['type'] : '',
        );
    }

    /**
     * User login form block
     *
     * @return bool|array
     */
    public static function login()
    {
        if (Pi::service('user')->hasIdentity()) {
            return false;
        }
        $form = new LoginForm('login');
        $form->setAttribute(
            'action',
            Pi::service('url')->assemble(
                'sysuser',
                array(
                    'module'        => 'system',
                    'controller'    => 'login',
                    'action'        => 'process',
                )
            )
        );

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
