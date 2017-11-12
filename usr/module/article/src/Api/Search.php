<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    protected function buildUrl(array $item, $table = '')
    {
        $link = Pi::service('url')->assemble(
            'article',
            array('module' => $this->module, 'id' => $item['id'])
        );

        return $link;
    }
}
