<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractComment;
use Module\Article\Entity;

/**
 * Comment target callback handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Comment extends AbstractComment
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

        // Get articles
        $where   = array('id' => (array) $item);
        $columns = array('id', 'subject', 'time_publish', 'uid');
        $items = Entity::getAvailableArticlePage(
            $where, 
            1, 
            null, 
            $columns, 
            null,
            $this->module
        );
        
        foreach ($items as $item) {
            $id          = $item['id'];
            $result[$id] = array(
                'id'    => $id,
                'title' => $item['subject'],
                'url'   => Pi::api('api', $this->module)->getUrl('detail', array(
                    'id'   => $id,
                    'time' => $item['time_publish'],
                ), $item),
                'uid'   => $item['uid'],
                'time'  => $item['time_publish'],
            );
        }

        if (is_scalar($item)) {
            $result = $result[$item];
        }

        return $result;
    }
}
