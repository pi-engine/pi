<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * User Event Handler
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class Event extends AbstractApi
{
    /**
     * User register event
     *
     * @param int $uid
     */
    public function userRegister($uid)
    {
    }

    /**
     * User activate event
     *
     * @param int $uid
     */
    public function userActivate($uid)
    {
    }

    /**
     * User update event
     *
     * @param int $uid
     */
    public function userUpdate($uid)
    {
        $this->updatePersist($uid);
    }

    /**
     * User enable event
     *
     * @param int $uid
     */
    public function userEnable($uid)
    {
    }

    /**
     * User disable event
     *
     * @param int $uid
     */
    public function userDisable($uid)
    {
    }

    /**
     * User delete event
     *
     * @param int $uid
     */
    public function userDelete($uid)
    {
    }

    /**
     * User name change event
     *
     * @param array $params
     */
    public function nameChange($params)
    {
        if (!empty($params)) {
            Pi::service('audit')->log('user-name-change', $params);

            $this->updatePersist($params['uid'], 'name', $params['new_name']);
        }
    }

    /**
     * User email change event
     *
     * @param array $params
     */
    public function emailChange($params)
    {
        if (!empty($params)) {
            Pi::service('audit')->log('user-email-change', $params);

            $this->updatePersist($params['uid'], 'email', $params['new_email']);
        }
    }

    /**
     * User avatar change event
     *
     * @param int $uid
     */
    public function avatarChange($uid)
    {
        $this->updatePersist($uid);
    }

    /**
     * User password change change event
     *
     * @param int $uid
     */
    public function passwordChange($uid)
    {
        if ($uid) {
            Pi::service('audit')->log('user-password-change', $uid);
        }
    }

    /**
     * User role assign change event
     *
     * @param int $uid
     */
    public function roleAssign($uid)
    {
    }

    /**
     * User role remove change event
     *
     * @param int $uid
     */
    public function roleRemove($uid)
    {
    }

    /**
     * User login event
     *
     * @param array $params
     */
    public function userLogin($params)
    {
        if (isset($params['uid']) && $params['uid']) {
            // Set ip login
            $ipLogin = Pi::user()->getIp();
            Pi::user()->data()->set(
                $params['uid'],
                'last_login_ip',
                $ipLogin,
                'user'
            );

            // Set login count
            Pi::user()->data()->increment($params['uid'], 'count_login', 1);

            // Set login time
            Pi::user()->data()->set(
                $params['uid'],
                'last_login',
                time(),
                'user'
            );
        }
    }

    /**
     * User logout event
     *
     * @param int $uid
     */
    public function userLogout($uid)
    {
    }

    /**
     * Update user persistent data
     *
     * @param int           $uid
     * @param string|null   $field
     * @param mixed         $value
     *
     * @return void
     */
    protected function updatePersist($uid, $field = null, $value = null)
    {
        if ($uid != Pi::service('user')->getId()) {
            return;
        }

        Pi::service('user')->setPersist($field, $value);

        return;
    }
}
