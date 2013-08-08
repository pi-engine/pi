<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo;

class Service
{
    protected static $module = 'demo';

    /**
     * Fetch content of an item from a type of moldule content
     * by calling Module\ModuleName\Service::content()
     *
     * @param array $variables Variables to be returned:
     *      title, summary, uid, user, etc.
     * @param array $conditions Associative array of conditions:
     *      item - item ID or ID list, module, type - optional, user, Where
     * @return array Associative array of returned content,
     *      or list of associative arry if $item is an array
     */
    public static function content(array $variables, array $conditions)
    {
        $module = empty($conditions['module'])
            ? static::$module : $conditions['module'];

        return array();
    }
}
