<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Comment;

use Pi;
use Pi\Application\AbstractComment;

/**
 * Comment target callback handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Article extends AbstractComment
{
    /** @var string */
    protected $module = 'article';

    /**
     * Get target data
     *
     * @param int|int[] $item Item id(s)
     *
     * @return array
     */
    public function get($item)
    {
        $result = array();
        $items = (array) $item;

        foreach ($items as $id) {
            $result[$id] = array(
                'title' => sprintf(__('Article %d'), $id),
                'url'   => Pi::service('url')->assemble(
                    'comment',
                    array(
                        'module'        => 'comment',
                        'controller'    => 'demo',
                        'id'            => $id,
                        'enable'        => 'yes',
                    )
                ),
                'uid'   => 1,
                'time'  => time(),
            );
        }

        if (is_scalar($item)) {
            $result = $result[$item];
        }

        return $result;
    }
}
