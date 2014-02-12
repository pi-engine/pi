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
 * User agent evaluation
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Agent extends AbstractAdapter
{
    /** @var string */
    const MESSAGE = 'Access denied by HTTP_USER_AGENT check';

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

        // No HTTP_USER_AGENT detected, return false upon DoS check,
        // otherwise null.
        if (empty($agent) || '-' == $agent) {
            return empty($options['dos']) ? null : false;
        }

        // Check bad bots
        if (!empty($options['bot'])) {
            $pattern = is_array($options['bot'])
                ? implode('|', $options['bot']) : $options['bot'];
            $status = preg_match('/' . $pattern . '/i', $agent) ? false : null;
            return $status;
        }

        return null;
    }
}
