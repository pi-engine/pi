<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page;

use Pi;
use Pi\Search\AbstractSearch;

/**
 * Class for module search
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Search extends AbstractSearch
{
    /** @var string Table name */
    protected $table = 'page';

    /** @var array Columns to fetch: column => meta field */
    protected $meta = array(
        'id'            => 'id',
        'title'         => 'title',
        'content'       => 'content',
        'time_created'  => 'time',
        'user'          => 'uid',
    );

    /**
     * {@inheritDoc}
     */
    public function buildLink(array $item)
    {
        $link = Pi::service('url')->assemble(
            'page-page',
            array('id' => $item['id'])
        );

        return $link;
    }
}
