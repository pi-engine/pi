<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Resource;

use Pi;

/**
 * User timeline handler
 *
 * Timeline APIs:
 *
 *   - get($limit[, $offset[, $condition]])
 *   - getCount([$condition]])
 *   - add(array(
 *          'uid'       => <uid>,
 *          'message'   => <message>,
 *          'module'    => <module-name>,
 *          'timeline'  => <timeline-name>,
 *          'link'      => <link-href>,
 *          'time'      => <timestamp>,
 *     ))
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractResource
{
    /**
     * Get timeline log list
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
        $result = Pi::api('timeline', 'user')->get($uid, $limit, $offset);

        return $result;
    }

    /**
     * Get timeline log count subject to type(s)
     *
     * @param int           $uid
     *
     * @return int
     */
    public function getCount($uid)
    {
        $result = 0;

        if (!$this->isAvailable()) {
            return $result;
        }
        $result = Pi::api('timeline', 'user')->getCount($uid);

        return $result;
    }

    /**
     * Write a timeline log
     *
     * Log array:
     *  - uid
     *  - message
     *  - timeline
     *  - module
     *  - link
     *  - time
     *
     * @param array $log
     * @return bool
     */
    public function add(array $log)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $result = Pi::api('timeline', 'user')->add($log);

        return $result;
    }
}
