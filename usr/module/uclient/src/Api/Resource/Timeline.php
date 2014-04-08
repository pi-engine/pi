<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Uclient\Api\Resource;

use Pi;
use Pi\User\Resource\Timeline as UserTimeline;

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
class Timeline extends UserTimeline
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
        return array();
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
        return 0;
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
     * @param array $params
     * @return bool
     */
    public function add(array $params)
    {
        if (!isset($params['uid'])) {
            $params['uid'] = Pi::service('user')->getId();
        }
        if (!isset($params['time'])) {
            $params['time'] = time();
        }
        $params['app_key'] = $this->options['app_key'];
        $uri = $this->options['api']['add'];
        $result = Pi::service('remote')
            ->setAuthorization($this->options['authorization'])
            ->post($uri, $params);
        $result = (bool) $result['status'];

        return $result;
    }
}
