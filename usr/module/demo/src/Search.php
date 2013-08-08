<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo;

use Pi;
use Pi\Application\Search as AbstractSearch;

class Search extends AbstractSearch
{
    protected static $module = 'demo';

    public static function index($queries, $type, $limit, $offset, $uid)
    {
        $router = Pi::engine()->application()->getRouter();
        $results = array();
        $max = 1000;
        $count = 0;
        for ($i = $offset; $i < $max; $i++) {
            if (++$count > $limit) break;
            $item = array(
                'uid'       => 1,
                'time'      => time(),
                'link'      => $router->assemble(
                    'default',
                    array(
                        'module'        => 'demo',
                        'controller'    => 'search',
                        'q'             => 'test-' . $i,
                    ),
                    'search'
                ),
                'title'     => sprintf(__('Test term %d'), $i),
                'content'   => sprintf(__('Some content for term %d'), $i),
            );
            $results[] = $item;
        }

        return $results;
    }
}
