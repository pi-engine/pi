<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Api;

/**
 * Abstract class for user callback
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractActivity extends AbstractApi
{
    /**
     * Get activity link and log list
     *
     * - Activity log meta:
     *   - time
     *   - log
     *
     *
     * @param int       $uid
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    public function get($uid, $limit, $offset = 0)
    {
        $link = $this->getLink($uid);
        $items = $this->getItems($uid, $limit, $offset);
        $result = array(
            'link'  => $link,
            'items' => $items,
        );

        return $result;
    }

    /**
     * Get activity item list
     *
     * - Activity item meta:
     *   - time
     *   - log
     *
     *
     * @param int       $uid
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    public function getItems($uid, $limit, $offset = 0)
    {
        $items = array();

        return $items;
    }


    /**
     * Get link to user activity full list
     *
     * @param int $uid
     *
     * @return string
     */
    public function getLink($uid)
    {
        $link = '';

        return $link;
    }
}
