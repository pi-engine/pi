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
 * User timeline handler
 *
 * Timeline APIs:
 *
 *   - timeline([$id])->get($limit[, $offset[, $condition]])
 *   - timeline([$id])->getCount([$condition]])
 *   - timeline([$id])->add(array(
 *          'message'   => <message>,
 *          'module'    => <module-name>,
 *          'type'      => <type>,
 *          'link'      => <link-href>,
 *          'time'      => <timestamp>,
 *     ))
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractResource
{
    /**
     * If user module available for time handling
     * @var bool|null
     */
    protected $isAvailable = null;

    /**
     * Get timeline log list
     *
     * @param int   $limit
     * @param int   $offset
     * @param array|string $type
     * @return array
     */
    public function get($limit, $offset = 0, array $type = array())
    {
        $result = array();

        if (!$this->isAvailable()) {
            return $result;
        }
        $model = Pi::model('timeline_log', 'user');
        $select = $model->select();
        if ($type) {
            $select->where(array('timeline' => $type));
        }
        $select->limit($limit)->offset($offset)->order('time DESC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $result[] = (array) $row;
        }

        return $result;
    }

    /**
     * Get timeline log count subject to type(s)
     *
     * @param array|string $type
     *
     * @return int
     */
    public function getCount(array $type = array())
    {
        $model = Pi::model('timeline_log', 'user');
        $select = $model->select();
        if ($type) {
            $select->where(array('timeline' => $type));
        }
        $rowset = $model->selectWith($select);
        $result = $rowset->count();

        return $result;

    }

    /**
     * Check if relation function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        if (null === $this->isAvailable) {
            $this->isAvailable = Pi::service('module')->isActive('user');
        }

        return $this->isAvailable;
    }

    /**
     * Write a timeline log
     *
     * Log array:
     *  - message
     *  - type
     *  - module
     *  - link
     *  - time
     *
     * @param array $log
     * @return bool
     */
    public function add($log)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if (!isset($log['uid'])) {
            $log['uid'] = $this->model->id;
        }
        if (!isset($log['time'])) {
            $log['time'] = time();
        }
        $row = Pi::model('time_log', 'user')->createRow($log);
        $id = $row->save();

        return $id;
    }

    /**
     * Placeholder for APIs
     *
     * @param string $method
     * @param array $args
     * @return bool|void
     */
    public function __call($method, $args)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);

        return null;
    }
}
