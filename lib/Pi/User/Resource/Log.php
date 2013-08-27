<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Resource;

use Pi;

/**
 * User action log handler
 *
 * Log APIs:
 *
 * - add($uid, $action, $data, $time)
 * - get($uid, $action, $limit, $offset)
 * - getLast($uid, $action)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Log extends AbstractResource
{
    /**
     * Get user log list
     *
     * @param int          $uid
     * @param int          $limit
     * @param int          $offset
     *
     * @return array
     */
    public function get($uid, $limit, $offset = 0)
    {
        $result = array();

        if (!$this->isAvailable()) {
            return $result;
        }
        $result = Pi::api('log', 'user')->get($uid, $limit, $offset);

        return $result;
    }

    /**
     * Get last log content
     *
     * @param int $uid
     * @param string $action
     *
     * @return mixed
     */
    public function getLast($uid, $action)
    {
        $list = $this->get($uid, $action, 1);
        $result = array_pop($list);

        return $result;
    }

    /**
     * Write an action log
     *
     * @param int $uid
     * @param string $action
     * @param string $data
     * @param int $time
     * @return bool
     */
    public function add($uid, $action, $data = '', $time = null)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('log', 'user')->add($uid, $action, $data, $time);

        return $result;
    }
}
