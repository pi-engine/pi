<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Cookie handling service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Cookie extends AbstractService
{
    /**
     * Set and send a cookie
     *
     * @param   string           $name
     * @param   string           $value
     * @param   int|string|array $expires
     * @param   string           $path
     * @param   string           $domain
     * @param   bool             $secure
     * @param   bool             $httponly
     *
     * @throws \InvalidArgumentException
     * @return  bool
     */
    public function set(
        $name,
        $value = null,
        $expires = null,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = false
    ) {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException("Cookie name cannot contain these characters: =,; \\t\\r\\n\\013\\014 ({$name})");
        }
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }

        if (is_array($expires)) {
            extract($expires);
        }
        if (!$path) {
            $path = Pi::host()->get('baseUrl') ?: '/';
        }
        $expires = time() + (int) $expires;

        //vd(compact('name', 'value', 'expires', 'path', 'domain', 'secure'));
        $result = setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);

        //vd($result);
        return $result;
    }

    /**
     * Fetch a cookie variable
     *
     * @param string $name
     * @param bool   $decode
     * @param bool   $onetime
     *
     * @return mixed
     */
    public function get($name, $decode = false, $onetime = false)
    {
        //vd($_COOKIE);
        $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        if ($onetime) {
            //$this->set($name, null, -1);
        }
        if ($value && $decode) {
            $value = json_decode($value, true);
        }

        return $value;
    }
}
