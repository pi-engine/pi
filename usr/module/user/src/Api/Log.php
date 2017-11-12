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
 * User log APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Log extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
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
     * Get timeline collection
     * @param $uid
     * @param $action
     * @param $module
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function getLogCollectionByUserId($uid, $action, $module = null, $data = null)
    {
        $model = Pi::model('log', 'user');
        $select = $model->select();
        $select->where(array('uid' => $uid))
            ->order('time DESC');

        $select->where->addPredicate(
            new \Zend\Db\Sql\Predicate\Like('action', $action . '%')
        );

        if($module){
            $select->where(array('module' => $module));
        }

        if($data){
            $select->where(array('data' => $data));
        }

        $rowset = $model->selectWith($select);

        return $rowset;
    }

    /**
     * Write a log
     *
     * @param int       $uid
     * @param string    $action
     * @param array     $log
     * @param int       $time
     *
     * @return bool
     */
    public function add($uid, $action, $log, $time = null)
    {
        if (!isset($log['time'])) {
            $log['time'] = time();
        }
        $row = Pi::model('log', 'user')->createRow($log);
        $row->save();

        return true;
    }
}
