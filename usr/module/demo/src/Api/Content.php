<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Api;

use Pi\Application\AbstractApi;

class Content extends AbstractApi
{
    protected $module = 'demo';

    /**
     * Fetch content of an item from a type of moldule content
     * by calling Module\ModuleName\Service::content()
     *
     * @param array $variables array of variables to be returned:
     *      title, summary, uid, user, etc.
     * @param array $conditions associative array of conditions:
     *      item - item ID or ID list, module, type - optional, user, Where
     * @return  array   associative array of returned content,
     *      or list of associative arry if $item is an array
     */
    public function load(array $variables, array $conditions)
    {
        $module = empty($conditions['module'])
            ? static::$module : $conditions['module'];

        return array();
    }
}
