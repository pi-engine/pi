<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
    /** @var  array Config for remote access */
    protected $config;

    /**
     * Get an option
     *
     * @return mixed|null
     */
    public function config()
    {
        if (null === $this->config) {
            $this->config = Pi::service('config')->load('module.uclient.php');
        }
        $args = func_get_args();
        $result = $this->config;
        foreach ($args as $name) {
            if (is_array($result) && isset($result[$name])) {
                $result = $result[$name];
            } else {
                $result = null;
                break;
            }
        }

        return $result;
    }

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
        $params['app_key'] = $this->config('app_key');

        $uri = $this->config('url', 'timeline', 'add');
        $result = Pi::service('remote')
            ->setAuthorization($this->config('authorization'))
            ->get($uri, $params);
        $result = (bool) $result['status'];

        return $result;
    }
}
