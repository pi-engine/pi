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
    protected $module = 'user';

    /**
     * Get user data
     * @param $uid
     * @param null $name
     * @param null $content
     * @return array
     */
    public function getData($uid, $name = null, $content = null)
    {
        $result = array();
        $where['uid'] = $uid;
        if ($name) {
            $where['name'] = $name;
        }
        if ($content) {
            $where['content'] = $content;
        }

        $model  = Pi::model('data', 'user');
        $select = $model->select()->where($where);
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result = $row->toArray();
        }

        return $result;
    }

    /**
     * Get user data by content
     *
     * @param $content
     * @return array|string
     */
    public function getDataByContent($content)
    {
        $result = '';
        $row = Pi::model('data', 'user')->find($content, 'content');
        if ($row) {
            $result = $row->toArray();
        }

        return $result;
    }

    /**
     * Set user data
     *
     * @param $uid  User id
     * @param $name Data type
     * @param null $module Default user
     * @return array
     */
    public function setData($uid, $name, $module = null)
    {
        $return = array(
            'content'   => '',
            'message' => '',
            'status'  => 0,

        );

        $time    = time();
        $content = md5(sprintf('%s%s%s', $uid, $name, $time));
        $module = $module ? $module : 'user';

        $model = Pi::model('data', $this->module);
        $where = array(
            'uid'  => $uid,
            'name' => $name,
        );

        $select = $model->select()->where($where);
        $row = $model->selectWith($select)->current();

        if (!$row->id) {
            // Insert a new data
            $row = $model->createRow(array(
                'uid'     => $uid,
                'module'  => $module,
                'name'    => $name,
                'time'    => time(),
                'content' => $content,
            ));
        } else {
            // Update
            $row = Pi::model('data', $this->module)->find($row->id, 'id');
            $row->time    = time();
            $row->content = $content;
        }

        try {
            $row->save();
        }  catch(\Exception $e) {
            $return['message'] = __('Set token failed');
            return $return;
        }

        $return['content'] = $content;
        $return['status']  = 1;
        $return['message'] = __('success');

        return $return;
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