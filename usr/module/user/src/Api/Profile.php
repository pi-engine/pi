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
 * User profile manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Check if user profile is complete
     *
     * @param int $uid
     *
     * @return bool
     */
    public function isComplete($uid = 0)
    {
        $result = true;
        $config = Pi::user()->config('require_profile_complete');
        if ($config) {
            $uid = $uid ?: Pi::service('user')->getId();
            $level = Pi::api('user', $this->module)->get($uid, 'level');
            $result = $level ? true : false;
        }

        return $result;
    }

    /**
     * Require user to complete profile to access a resource
     *
     * @param int           $uid
     * @param string|bool   $redirect
     *
     * @return void
     */
    public function requireComplete($uid = 0, $redirect = '')
    {
        if ($this->isComplete($uid)) {
            return;
        }

        if (false === $redirect) {
            $redirect = Pi::url('www');
        } elseif (!$redirect) {
            $redirect = Pi::service('url')->getRequestUri();
        }
        Pi::service('url')->redirect(
            Pi::service('url')->assemble(
                'user',
                array(
                    'controller' => 'register',
                    'action' => 'profile.complete',
                )
            ),
            $redirect
        );
    }
}