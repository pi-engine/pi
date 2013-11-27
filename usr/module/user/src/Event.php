<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User;

use Pi;

/**
 * User Event Handler
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class Event
{
    /**
     * User register event
     *
     * @param int $uid
     */
    public static function userRegister($uid)
    {

    }

    /**
     * User join community event
     *
     * @param int $uid
     */
    public static function joinCommunity($uid)
    {
        // Get community id
        $commnuityId = Pi::api('user', 'user')->get($uid, 'registered_source') ? : 16;
        $uri    = 'http://www.eefocus.com/passport/api.php';
        $params = array(
            'act' => 'join',
            'uid' => $uid,
            'pid' => $commnuityId
        );
        Pi::service('remote')->get($uri, $params);
    }

    /**
     * User enable event
     *
     * @param int $uid
     */
    public static function userEnable($uid)
    {

    }

    /**
     * User disable event
     *
     * @param int $uid
     */
    public static function userDisable($uid)
    {

    }

    /**
     * User delete event
     *
     * @param int $uid
     */
    public static function userDelete($uid)
    {

    }

    /**
     * User name change event
     *
     * @param int $uid
     */
    public static function nameChange($option)
    {
        if (isset($option['log_args']) &&
            $option['log_args']
        ) {
            Pi::service('audit')->log('reset-name', $option['log_args']);
        }
    }

    /**
     * User email change event
     *
     * @param int $uid
     */
    public static function emailChange($option)
    {
        if (isset($option['log_args']) &&
            $option['log_args']
        ) {
            Pi::service('audit')->log('reset-email', $option['log_args']);
        }
    }

    /**
     * User password change change event
     *
     * @param int $uid
     */
    public static function passwordChange($uid)
    {

    }

    /**
     * User role assign change event
     *
     * @param int $uid
     */
    public static function roleAssign($uid)
    {

    }

    /**
     * User role remove change event
     *
     * @param int $uid
     */
    public static function roleRemove($uid)
    {

    }

    /**
     * User login event
     *
     * @param int $uid
     */
    public static function userLogin($uid)
    {

    }

    /**
     * User logout event
     *
     * @param int $uid
     */
    public static function userLogout($uid)
    {

    }
}
