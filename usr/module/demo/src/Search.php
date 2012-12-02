<?php
/**
 * Demo module search class
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

namespace Module\Demo;
use Pi;
use Pi\Application\Search as AbstractSearch;

class Search extends AbstractSearch
{
    protected static $module = 'demo';

    public static function index($queries, $type, $limit, $offset, $uid)
    {
       // $params = compact('queries', 'type', 'limit', 'offset', 'uid');

        $router = Pi::engine()->application()->getRouter();
        $results = array();
        $max = 1000;
        $count = 0;
        for ($i = $offset; $i < $max; $i++) {
            if (++$count > $limit) break;
            $item = array(
                'uid'       => 1,
                'time'      => time(),
                'link'      => $router->assemble('default', array('module' => 'demo', 'controller' => 'search', 'q' => 'test-' . $i), 'search'),
                'title'     => sprintf(__('Test term %d'), $i),
                'content'   => sprintf(__('Some content for term %d'), $i),
            );
            $results[] = $item;
        }
        return $results;
    }
}
