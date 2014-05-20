<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Api;

use Pi;
use Pi\Search\AbstractSearch;
use Pi\Db\Sql\Where;
use Pi\Application\Model\Model;

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
    protected $table = 'post';

    /**
     * {@inheritDoc}
     */
    protected $searchIn = array(
        'content'
    );

    /**
     * {@inheritDoc}
     */
    protected $meta = array(
        'id'            => 'id',
        'content'       => 'content',
        'time'          => 'time',
        'uid'           => 'uid',
    );

    /**
     * {@inheritDoc}
     */
    protected function fetchResult(
        Model $model,
        Where $where,
        $limit  = 0,
        $offset = 0
    ) {
        $result = parent::fetchResult($model, $where, $limit, $offset);

        // Fetch users
        $users = array();
        array_walk($result, function ($data) use (&$users) {
            $users[$data['uid']] = array();
        });
        $users = Pi::service('user')->mget(array_keys($users), 'name');

        // Build title
        array_walk($result, function (&$data) use ($users) {
            $data['title'] = sprintf(__('Comment by %s'), $users[$data['uid']]);
        });

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function buildUrl(array $item)
    {
        $link = Pi::service('url')->assemble(
            'comment',
            array('controller' => 'post', 'id' => $item['id'])
        );

        return $link;
    }
}
