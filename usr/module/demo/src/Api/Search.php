<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi;
use Pi\Search\AbstractSearch;

class Search extends AbstractSearch
{
    /**
     * {@inheritDoc}
     */
    protected $module = 'demo';

    /**
     * {@inheritDoc}
     */
    public function query(
        $terms,
        $limit  = 0,
        $offset = 0,
        array $condition = array()
    ) {
        $results = array();
        $max = 1000;
        $count = 0;
        for ($i = $offset; $i < $max; $i++) {
            if (++$count > $limit) break;
            $item = array(
                'uid'       => 1,
                'time'      => time(),
                'url'       => Pi::service('url')->assemble(
                    'default',
                    array(
                        'module'        => 'demo',
                        'controller'    => 'search',
                        'q'             => 'test-' . $i,
                    )
                ),
                'title'     => sprintf(__('Test term %d'), $i),
                'content'   => sprintf(__('Some content for term %d'), $i),
            );
            $results[] = $item;
        }

        $result = $this->buildResult($max, $results);

        return $result;
    }
}
