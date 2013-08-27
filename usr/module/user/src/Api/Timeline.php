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
 * User timeline APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * Get timeline meta list
     *
     * @return array
     */
    public function getList()
    {
        $result = array();
        $list = Pi::registry('timeline', 'user')->read();
        foreach ($list as $name => $meta) {
            $result[$name] = array(
                'title' => $meta['title'],
                'icon'  => $meta['icon'],
            );
        }

        return $result;
    }

    /**
     * Get user timeline log list
     *
     * Log array: time, message
     *
     * @param int       $uid
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    public function get($uid, $limit, $offset = 0)
    {
        $result = array();

        $model = Pi::model('timeline_log', 'user');
        $select = $model->select();
        $select->where(array('uid' => $uid))
            ->columns(array('time', 'message', 'link', 'timeline', 'module'))
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
     * Get timeline log count of a user
     *
     * @param int $uid
     *
     * @return int
     */
    public function getCount($uid)
    {
        $model = Pi::model('timeline_log', 'user');
        $select = $model->select()->where(array('uid' => $uid))
            ->columns(array(
                'count' => Pi::db()->expression('COUNT(*)')
            ));
        $row = $model->selectWith($select)->current();
        $count = (int) $row['count'];

        return $count;
    }

    /**
     * Write a timeline log
     *
     * Log array:
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
        if (!isset($log['time'])) {
            $log['time'] = time();
        }
        $row = Pi::model('timeline_log', 'user')->createRow($log);
        $row->save();

        return true;
    }
}
