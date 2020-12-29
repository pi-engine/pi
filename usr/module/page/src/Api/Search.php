<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Api;

use Pi;
use Pi\Application\Api\AbstractSearch;

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
            'user'         => 'uid',
        ];

    /**
     * {@inheritDoc}
     */
    protected function buildUrl(array $item, $table = '')
    {
        $url = Pi::api('api', $this->module)->url($item['id']);

        return $url;
    }
}
