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

namespace Pi;

use Pi;
use Zend\Escaper\Escaper;

class Security
{
    static protected $escaper;

    /**
     * Header outputs on deny
     *
     * @param string $message The message to be displayed
     * @return void
     */
    public static function deny($message = '')
    {
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            header('Status: 403 Forbidden');
        } else {
            header('HTTP/1.1 403 Forbidden');
        }
        exit('Access denied' . ($message ? ': ' . $message : '.'));
    }

    /**#@++
     * Check security settings
     *
     * Policy: Returns TRUE will cause process quite and the current request will be approved; returns FALSE will cause process quit and request will be denied
     */

    /**
     * Check for IPs
     */
    public static function ip($options = null)
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
    public static function globals($options = null)
    {
        $items = is_array($options) ? $options : explode(',', $options);
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
     * Remove path prefix for security considerations
     *
     * @staticvar array $paths
     * @param string $str
     * @return string
     */
    public static function sanitizePath($str)
    {
        static $paths;

        if (!isset($paths)) {
            // Loads all path settings from host data
            $paths = Pi::host()->get('path');
            $lengths = array();
            foreach ($paths as $root => $v) {
                $lengths[] = strlen($v);
            }
            // Sort the paths by their lengths in reverse
            array_multisort($lengths, SORT_NUMERIC, SORT_DESC, $paths);
        }
        if (DIRECTORY_SEPARATOR != '/') {
            $str = str_replace(DIRECTORY_SEPARATOR, '/', $str);
        }
        foreach ($paths as $root => $v) {
            if (empty($v) || empty($root)) {
                continue;
            }
            $str  = str_replace($v . '/', $root . '/', $str);
        }
        return $str;
    }

    /**
     * Remove DB database name and table prefix for security considerations
     *
     * @param string $str
     * @return string
     */
    public static function sanitizeDb($str)
    {
        $pattern = '/\b' . preg_quote(Pi::db()->getTablePrefix()) . '/i';
        $return = preg_replace($pattern, '', $str);
        return $return;
    }

    /**
     * Get escaper, and escape HTML content if specified
     *
     * @param string|null $content
     * @return Escaper|string
     */
    public static function escape($content = null)
    {
        if (!static::$escaper) {
            static::$escaper = new Escaper(Pi::config('charset'));
        }
        if (null === $content) {
            return static::$escaper;
        }
        return static::$escaper->escapeHtml($content);
    }

    /**
     * Magic method to access custom security settings
     *
     * @param string $method The security setting to be checked
     * @param array  $args  arguments for the setting
     */
    public static function __callStatic($method, $args = null)
    {
        $class = __NAMESPACE__ . '\\Security\\' . ucfirst($method);
        if (class_exists($class) && is_subclass_of($class, __NAMESPACE__ . '\Security\\AbstractSecurity')) {
            $options = $args[0];
            return $class::check($options);
        }
        return null;
    }
    /*#@-*/
}