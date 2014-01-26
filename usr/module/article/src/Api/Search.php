<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Search\AbstractSearch;

/**
 * Class for module search
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Search extends AbstractSearch
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'article';

    /**
     * {@inheritDoc}
     */
    protected $searchIn = array(
        'subject',
        'subtitle',
        'summary',
        'content'
    );

    /**
     * {@inheritDoc}
     */
    protected $meta = array(
        'id'            => 'id',
        'subject'       => 'title',
        'summary'       => 'content',
        'time_publish'  => 'time',
        'uid'           => 'uid',
    );
    
    /**
     * {@inheritDoc}
     */
    protected function buildContent($content = '')
    {
        $content = mb_substr(strip_tags($content), 0, 255, 'utf-8');
        return $content;
    }

    /**
     * {@inheritDoc}
     */
    protected function buildUrl(array $item)
    {
        $link = Pi::service('url')->assemble(
            'article-article',
            array('id' => $item['id'])
        );

        return $link;
    }
}
