<?php
/**
 * Pi Engine local (or built-in) user service
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
 * @package         Pi\User
 */

namespace Pi\User;

use Pi;

class Service extends AbstractService
{
    /**
     * Get user full name
     *
     * @param string $identity
     * @return string
     */
    public function getName($identity = null)
    {
        $identity = $identity ?: $this->identity;
        $user = new User($identity);
        return $user->name;
    }

    /**
     * Get user profile URL
     *
     * @param string $identity
     * @return string
     */
    public function getProfileUrl($identity = null)
    {
        $identity = $identity ?: $this->identity;
        $url = Pi::service('url')->assemble('user', array(
            'controller'    => 'profile',
            'identity'      => $identity,
        ));
        $url = Pi::url($url, true);
        return $url;
    }

    /**
     * Method handler allows a shortcut
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        trigger_error(sprintf(__CLASS__ . '::%s is not defined yet.', $method), E_USER_NOTICE);
        return 'Not defined';
    }
}