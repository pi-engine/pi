<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi;
use Pi\Application\Api\AbstractContent;

/**
 * Public API for content fetch
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Content extends AbstractContent
{
    /**
     * {@inheritDoc}
     */
    protected $module = 'demo';

    /**
     * {@inheritDoc}
     */
    protected $table = 'page';

    /**
     * {@inheritDoc}
     */
    protected $meta
        = [
            'id'           => 'id',
            'title'        => 'title',
            'content'      => 'content',
            'time_created' => 'time',
            'uid'          => 'uid',
        ];

    /**
     * {@inheritDoc}
     */
    public function ____getList(
        array $variables,
        array $conditions,
        $limit = 0,
        $offset = 0,
        $order = []
    )
    {
        $result = [];

        for ($i = 1; $i <= $limit; $i++) {
            $item     = [
                'id'      => $i,
                'title'   => sprintf('Demo title %d', $i),
                'content' => sprintf('Demo content %d', $i),
                'url'     => Pi::url('www/demo/content/' . $i),
                'uid'     => rand(1, 5),
                'time'    => time() - rand(0, 1000),
            ];
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Create link
     *
     * @param array $item
     *
     * @return string
     */
    protected function buildUrl(array $item)
    {
        $url = Pi::service('url')->assemble(
            'default',
            [
                'module'     => $this->module,
                'controller' => 'page',
                'action'     => 'view',
                'id'         => $item['id'],
            ]
        );

        return $url;
    }
}