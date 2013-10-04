<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Comment;

use Pi;
use Pi\Application\AbstractComment;

class Article extends AbstractComment
{
    protected $module = 'comment';

    public function get($item)
    {
        $result = array();
        $items = (array) $item;

        foreach ($items as $id) {
            $result[$id] = array(
                'title' => sprintf(__('Demo article %d'), $id),
                'url'   => Pi::service('url')->assemble(
                    'comment',
                    array(
                        'module'    => 'comment',
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
