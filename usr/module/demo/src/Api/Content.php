<?php
/**
 * Demo module content API class
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
 * @since           3.0
 * @package         Module\Demo
 * @version         $Id$
 */

namespace Module\Demo\Api;

use Pi\Application\AbstractApi;

class Content extends AbstractApi
{
    protected $module = 'demo';

    /**
     * Fetch content of an item from a type of moldule content by calling Module\ModuleName\Service::content()
     *
     * @param array $variables array of variables to be returned: title, summary, uid, user, etc.
     * @param array $conditions associative array of conditions: item - item ID or ID list, module, type - optional, user, Where
     * @return  array   associative array of returned content, or list of associative arry if $item is an array
     */
    public function load(array $variables, array $conditions)
    {
        $module = empty($conditions['module']) ? static::$module : $conditions['module'];
        return array();
    }
}
