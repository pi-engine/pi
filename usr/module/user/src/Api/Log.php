<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User log APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Log extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * Get user log list
     *
     * Log array: time, data
     *
     * @param int       $uid
     * @param string    $action
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    public function get($uid, $action, $limit, $offset = 0)
    {
        $result = array();

        $model = Pi::model('log', 'user');
        $select = $model->select();
        $select->where(array('uid' => $uid, 'action' => $action))
            ->columns(array('time', 'data'))
            ->limit($limit)
            ->offset($offset)
            ->order('id DESC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }

    /**
     * Write a log
     *
     * @param array $log
     * @return bool
     */
    public function add($uid, $action, $data, $time = null)
    {
        if (!isset($log['time'])) {
            $log['time'] = time();
        }
        $row = Pi::model('timeline_log', 'user')->createRow($log);
        $row->save();

        return true;
    }
}
