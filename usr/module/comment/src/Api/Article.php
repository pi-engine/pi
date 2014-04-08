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
use Pi\Application\Api\AbstractComment;

/**
 * Comment target callback handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Article extends AbstractComment
{
    /** @var string */
    protected $module = 'comment';

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
                'id'    => $id,
                'title' => sprintf(__('Demo article %d'), $id),
                'url'   => Pi::service('url')->assemble(
                    'comment',
                    array(
                        'module'        => 'comment',
                        'controller'    => 'demo',
                        'id'            => $id,
                        'enable'        => 'yes',
                    )
                ),
                'uid'   => rand(1, 5),
                'time'  => time(),
            );
        }

        if (is_scalar($item)) {
            $result = $result[$item];
        }

        return $result;
    }
}
