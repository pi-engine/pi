<?php
/**
 * Security check for Pi Engine
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
 * @since           1.0
 * @package         Security
 * @version         $Id$
 */

namespace Pi\Security;

class Agent extends AbstractSecurity
{
    const MESSAGE = "Access denied by HTTP_USER_AGENT check";

    /**
     * Check security settings
     *
     * Policy: Returns TRUE will cause process quite and the current request will be approved; returns FALSE will cause process quit and request will be denied
     */
    public static function check($options = null)
    {
        $key = 'HTTP_USER_AGENT';
        $agent = '';
        if (isset($_SERVER[$key])) {
            $agent = $_SERVER[$key];
        } elseif (isset($_ENV[$key])) {
            $agent = $_ENV[$key];
        } elseif (getenv($key)) {
            $agent = getenv($key);
        } elseif (function_exists('apache_getenv')) {
            $agent = apache_getenv($key, true);
        }

        // No HTTP_USER_AGENT detected, return false upon DoS check, otherwise null.
        if (empty($agent) || '-' == $agent) {
            return empty($options['dos']) ? null : false;
        }

        // Check bad bots
        if (!empty($options['bot'])) {
            $pattern = is_array($options['bot']) ? implode("|", $options['bot']) : $options['bot'];
            $status = preg_match('/' . $pattern . '/i', $agent) ? false : null;
            return $status;
        }

        return null;
    }
}
