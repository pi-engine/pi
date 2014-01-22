<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Api;

use Pi;
use Pi\Application\AbstractContent;

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
    public function getList(
        array $variables,
        array $conditions,
        $limit  = 0,
        $offset = 0,
        $order  = array()
    ) {
        $result = array();

        for ($i = 1; $i <= $limit; $i++) {
            $item = array(
                'id'        => $i,
                'title'     => sprintf('Demo title %d', $i),
                'content'   => sprintf('Demo content %d', $i),
                'link'      => Pi::url('www/demo/content/' . $i),
                'uid'       => rand(1, 5),
                'time'      => time() - rand(0, 1000),
            );
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
    protected function buildLink(array $item)
    {
        $link = Pi::service('url')->assemble(
            'article-article',
            array('id' => $item['id'])
        );

        return $link;
    }
}