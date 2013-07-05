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
 * @package         Pi\Kernel
 * @subpackage      Security
 * @version         $Id$
 */

namespace Pi\Security;

use Pi;

class Security
{
    /**#@++
     * Check security settings
     *
     * Policy: Returns TRUE will cause process quite and the current request will be approved; returns FALSE will cause process quit and request will be denied
     */
    /**
     * Check for IPs
     */
    public static function ip($options = array())
    {
        $clientIp = array();
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $clientIp[] = $_SERVER['REMOTE_ADDR'];
        }

        // Find out IP behind proxy
        if (!empty($options['checkProxy'])) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $clientIp[] = $_SERVER['HTTP_CLIENT_IP'];
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $clientIp[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        $clientIp = array_unique($clientIp);

        // Check upon bad IPs
        if (!empty($options['bad'])) {
            $pattern = is_array($options['bad']) ? implode('|', $options['bad']) : $options['bad'];
            foreach ($clientIp as $ip) {
                if (preg_match('/' . $pattern . '/', $ip)) {
                    return false;
                }
            }
        }

        // Check upon good IPs
        if (!empty($options['good'])) {
            $pattern = is_array($options['good']) ? implode('|', $options['good']) : $options['good'];
            foreach ($clientIp as $ip) {
                if (preg_match('/' . $pattern . '/', $ip)) {
                    return true;
                }
            }
        }

        return null;
    }

    /**
     * Check for super globals
     */
    public static function globals($options = array())
    {
        $items = $options;
        array_walk($items, 'trim');
        $items = array_filter($items);
        foreach ($items as $item) {
            if (isset($_REQUEST[$item])) {
                return false;
            }
        }

        return null;
    }

    /**
     * Magic method to access custom security settings
     *
     * @param string $method The security setting to be checked
     * @param array  $args  arguments for the setting
     */
    public static function __callStatic($method, $args = array())
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($method);
        if (class_exists($class) && is_subclass_of($class, __NAMESPACE__ . '\AbstractAdapter')) {
            $options = $args[0];
            return $class::check($options);
        }
        return null;
    }
    /*#@-*/
}