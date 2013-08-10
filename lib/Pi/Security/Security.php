<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Security;

use Pi;

/**
 * Security handler with variety of adapters
 *
 *
 * Policy with different result from each adapter:
 *
 * - true: following evaluations will be terminated and current request
 *      is approved
 * - false: following evaluations will be terminated and current request
 *      is denied
 * - null: continue
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Security
{
    /**
     * Check against IPs
     *
     * @param array $options
     * @return bool|null
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
            $pattern = is_array($options['bad'])
                ? implode('|', $options['bad']) : $options['bad'];
            foreach ($clientIp as $ip) {
                if (preg_match('/' . $pattern . '/', $ip)) {
                    return false;
                }
            }
        }

        // Check upon good IPs
        if (!empty($options['good'])) {
            $pattern = is_array($options['good'])
                ? implode('|', $options['good']) : $options['good'];
            foreach ($clientIp as $ip) {
                if (preg_match('/' . $pattern . '/', $ip)) {
                    return true;
                }
            }
        }

        return null;
    }

    /**
     * Check against super globals contamination
     *
     * @param array $globals Name of super globals to check
     * @return bool|null
     */
    public static function globals($globals = array())
    {
        array_walk($globals, 'trim');
        $items = array_filter($globals);
        foreach ($items as $item) {
            if (isset($_REQUEST[$item])) {
                return false;
            }
        }

        return null;
    }

    /**
     * Magic method to access against custom security adapters
     *
     * @param string $method Security adapter to be checked
     * @param array  $args  Arguments for the setting
     * @return bool|null
     */
    public static function __callStatic($method, $args = array())
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($method);
        if (class_exists($class)
            && is_subclass_of($class, __NAMESPACE__ . '\AbstractAdapter')
        ) {
            $options = $args[0];
            return $class::check($options);
        }

        return null;
    }
}
