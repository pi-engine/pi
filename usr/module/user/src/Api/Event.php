<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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

            // Check two-factor authentication
            /* if (Pi::config('two_factor_authentication')) {

                // Set two-factor authentication not passed
                Pi::user()->data()->set(
                    $params['uid'],
                    'two_factor_check',
                    0,
                    'user'
                );

                // Set redirect
                if (isset($params['redirect']) && !empty($params['redirect'])) {
                    Pi::user()->data()->set(
                        $params['uid'],
                        'redirect',
                        json_encode($params['redirect']),
                        'user'
                    );
                }
            } */
        }
    }

    /**
     * User logout event
     *
     * @param int $uid
     */
    public function userLogout($uid)
    {
        // Check two-factor authentication
        if (Pi::config('two_factor_authentication')) {

            // Remove two-factor authentication not passed
            Pi::user()->data()->delete(
                $uid,
                'two_factor_check',
                'user'
            );

            // Remove redirect
            Pi::user()->data()->delete(
                $uid,
                'redirect',
                'user'
            );
        }
    }

    /**
     * Update user persistent data
     *
     * @param int $uid
     * @param string|null $field
     * @param mixed $value
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
