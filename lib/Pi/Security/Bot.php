<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Security;

/**
 * Search engine bot check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Bot extends AbstractAdapter
{
    /** @var string */
    const MESSAGE = 'Access denied by bot check';

    /**
     * {@inheritDoc}
     */
    public static function check($options = [])
    {
        if (!$options) {
            return null;
        }
        $key   = 'HTTP_USER_AGENT';
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
            return null;
        }
        // Check bad bots
        $pattern = implode('|', $options);
        $status  = preg_match('/' . $pattern . '/i', $agent) ? false : null;

        return $status;
    }
}
