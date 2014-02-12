<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Security;

/**
 * DoS check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Dos extends AbstractAdapter
{
    /** @var string */
    const MESSAGE = 'Access denied by DoS check';

    /**
     * {@inheritDoc}
     */
    public static function check($options = array())
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
        if (empty($agent) || '-' == $agent) {
            return false;
        }

        return null;
    }
}
