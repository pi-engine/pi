<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * User timeline APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Get timeline meta list
     *
     * @return array
     */
    public function getList()
    {
        $result = [];
        $list   = Pi::registry('timeline', 'user')->read();
        foreach ($list as $name => $meta) {
            $result[$name] = [
                'title' => $meta['title'],
                'icon'  => $meta['icon'],
            ];
        }

        return $result;
    }

    /**
     * Get user timeline log list
     *
     * Log array: time, message
     *
     * @param int $uid
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function get($uid, $limit, $offset = 0)
    {
        $result = [];

        $model  = Pi::model('timeline_log', 'user');
        $select = $model->select();
        $select->where(['uid' => $uid])
            ->columns(['time', 'message', 'link', 'timeline', 'module'])
            ->limit($limit)
            ->offset($offset)
            ->order('time DESC');
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
        /*
        $select = $model->select()->where(array('uid' => $uid))
            ->columns(array(
                'count' => Pi::db()->expression('COUNT(*)')
            ));
        $row = $model->selectWith($select)->current();
        $count = (int) $row['count'];
        */
        $count = $model->count(['uid' => $uid]);

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

    /**
     * delete a timeline log
     *
     * Log array:
     *  - module
     *  - data
     *
     * @param array $log
     * @return bool
     */
    public function delete(array $log)
    {
        $id  = $log['data'];
        $row = Pi::model('timeline_log', 'user')->delete(['module' => $log['module'], 'data' => "{\"comment\":$id}"]);
        return true;
    }
}
