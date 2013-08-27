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
 * User data manipulation APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class UserData extends AbstractApi
{
    /**
     * Module name
     *
     * @var string
     */
    protected $module = 'user';

    protected $meta = array(
        'id',
        'module',
        'name',
        'time',
        'content',
    );

    /**
     * Get user data to conditions
     *
     * @param array $condition
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @return mixed
     */
    public function getData(
        $condition = array(),
        $limit     = 0,
        $offset    = 0,
        $order     = ''
    ){
        $meta = $this->meta;
        $result = array();
        $data = array();
        foreach ($condition as $key => $value) {
            if (isset($meta[$key])) {
                $data[$key] = $value;
            }
        }

        $model = Pi::model('user', 'userdata');

        $select = $model->select();
        if ($data) {
            $select->where($data);
        }
        if ($order) {
            $select->order($order);
        }
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $result = $model->selectWith($select)->toArray();

        return $result;
    }

    /**
     * Set user data
     *
     * @param $uid
     * @param $module
     * @param $name
     * @param $content
     */
    public function setData($uid, $module, $name, $content, $time = '')
    {
        if (!$time) {
            $time = time();
        }

        $model = Pi::model('user', 'userdata');
        $where = array(
            'uid'     => $uid,
            'module'  => $module,
            'name'    => $name,
            'content' => $content,
            'time'    => $time,
        );
        $select = $model->select()->where($where);
        $rowset = $model->selectWith($select)->current();

        $result = array();
        if (!$rowset->id) {
            // Insert data
            $result = $this->insertData($uid, $model, $name, $content);
        } else {
            // Update data
            $result = $this->updateData($uid, $model, $name, $content);
        }

        return $result;
    }

    /**
     * Add a new data
     *
     * @param $uid
     * @param $module
     * @param $name
     * @param $content
     * @param string $time
     * @return mixed
     */
    public function insertData($uid, $module, $name, $content, $time = '')
    {
        if (!$time) {
            $time = time();
        }
        $model = Pi::model('user', 'userdata');
        $data = array(
            'uid'     => $uid,
            'module'  => $module,
            'name'    => $name,
            'content' => $content,
            'time'    => $time,
        );
        $row = $model->createRow($data);
        $row->save();

        return $row->toArray();
    }

    /**
     * Update user data
     *
     * @param $uid
     * @param $module
     * @param $name
     * @param $content
     * @param string $time
     * @return mixed
     */
    public function updateData($uid, $module, $name, $content, $time = '')
    {
        if (!$time) {
            $time = time();
        }

        $model = Pi::model('user', 'userdata');
        $row = $model->select(array(
            'uid'    => $uid,
            'module' => $module,
            'name'   => $name,
            'time'   => $time,
        ))->current();

        $row->assign(array(
            'content' => $content,
        ));

        $row->save();

        return $row->toArray();
    }

    /**
     * Delete user data by id
     *
     * @param $id
     * @return int
     */
    public function deleteData($id)
    {
        $status = Pi::model('data', 'user')->delete(
            array(
                'id' => $id
            )
        );

        return $status;
    }
}