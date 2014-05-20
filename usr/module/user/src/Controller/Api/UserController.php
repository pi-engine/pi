<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ApiController;

/**
 * User webservice controller
 *
 * Methods:
 *
 * - delete: <id>
 * - get: <id>, array(<field>)
 * - insert: array(<field> => <value>)
 * - list: <limit>, <offset>, <order>, array(<queryKey:queryValue>), array(<field>)
 * - patch: <id>, array(<field> => <value>)
 * - undelete: <id>
 * - update: <id>, array(<field> => <value>)
 *
 *
 * - mdelete: array(<id>)
 * - mget: array(<id>), array(<field>)
 * - mundelete: array(<id>)
 *
 * - meta
 * - count: array(<queryKey:queryValue>)
 * - activate: <id>
 * - enable: <id>
 * - disable: <id>
 *
 * - mactivate: array(<id>)
 * - menable: array(<id>)
 * - mdisable: array(<id>)
 *
 * - check: array(<queryKey:queryValue>)
 *
 * @see https://developers.google.com/admin-sdk/directory/v1/reference/users
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserController extends ApiController
{
    protected $protectedFields = array(
        'credential', 'salt'
    );

    /**
     * Placeholder
     *
     * @return array
     */
    public function indexAction()
    {
        return array('status' => 1);
    }

    /**
     * Deletes a user
     *
     * @return array
     */
    public function deleteAction()
    {
        $response   = array();
        $uid        = $this->params('id');
        $result     = Pi::service('user')->delete($uid);
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }

    /**
     * Gets a user with specified fields
     *
     * @return array
     */
    public function getAction()
    {
        $uid        = $this->params('id');
        $field      = $this->params('field');
        $fields     = $this->splitString($field);
        $fields     = array_diff($fields, $this->protectedFields);
        $result     = (array) Pi::service('user')->get($uid, $fields, null, true);
        $response   = $result;

        return $response;
    }

    /**
     * Gets multiple users with specified fields
     *
     * @return array
     */
    public function mgetAction()
    {
        $uid        = $this->params('id');
        $field      = $this->params('field');
        $uids       = $this->splitString($uid);
        $fields     = $this->splitString($field);
        $fields     = array_diff($fields, $this->protectedFields);
        $result     = Pi::service('user')->mget($uids, $fields, null, true);
        $response   = $result;

        return $response;
    }

    /**
     * Gets a list of users
     *
     * @return array
     */
    public function listAction()
    {
        $limit  = $this->params('limit', 10);
        $offset = $this->params('offset', 0);
        $order  = $this->params('order');
        $query  = $this->params('query');
        $field  = $this->params('field');

        $order  = $this->splitString($order);
        $fields = $this->splitString($field);
        $fields = array_diff($fields, $this->protectedFields);
        $query  = $this->canonizeQuery($query);

        $where  = $this->canonizeCondition($query);
        $users  = Pi::service('user')->getList(
            $where,
            $limit,
            $offset,
            $order,
            $fields
        );

        return $users;
    }

    /**
     * Check username, email, display name exist
     *
     * @FIXME The return data structure should be
     *          `array('status' => 1|0, 'data' => 1|0)`,
     *          `status` for query status, `data` 1 for exist and 0 for not;
     *
     * @return array
     */
    public function checkExistAction()
    {
        $result = array(
            'status' => 1,
        );

        $query  = $this->params('query');
        $query  = $this->canonizeQuery($query);
        foreach (array('identity', 'email', 'name') as $param) {
            $val = $this->params($param);
            if ($val) {
                $query[$param] = $val;
            }
        }
        if (!$query) {
            return $result;
        }
        $where = Pi::db()->where();
        foreach ($query as $key => $val) {
            $where->equalTo($key, $val)->or;
        }

        $count = Pi::model('user_account')->count($where);
        $result = array(
            'status'    => $count ? 1 : 0, // @FIXME: for backward compat
            'data'      => $count ? 1 : 0,
        );

        return $result;

        /*

        $identity = _get('identity');
        $email    = _get('email');
        $name     = _get('name');

        if (!$identity && !$email && !$name ) {
            return $result;
        }

        $model = Pi::model('user_account');
        if ($identity) {
            $row = $model->find($identity, 'identity');
            $result['status'] = $row ? 1 : 0;

            return $result;
        }

        if ($email) {
            $row = $model->find($email, 'email');
            $result['status'] = $row ? 1 : 0;

            return $result;
        }

        if ($name) {
            $row = $model->find($name, 'name');
            $result['status'] = $row ? 1 : 0;

            return $result;
        }
        */
    }

    /**
     * Get user meta
     *
     * @return array
     */
    public function metaAction()
    {
        $response = array();
        $meta = Pi::registry('field', 'user')->read('', 'display');
        //$meta = Pi::registry('field', 'user')->read();
        array_walk($meta, function ($data) use (&$response) {
            $field = $data['name'];
            $response[$field] = array(
                'name'  => $field,
                'title' => $data['title'],
            );
            if ('compound' == $data['type']/* || 'custom' == $data['type']*/) {
                $fields = Pi::registry('compound_field', 'user')->read($field);
                array_walk($fields, function ($fData) use (&$response) {
                    $field = $fData['compound'];
                    $response[$field]['field'][$fData['name']] = array(
                        'name'  => $fData['name'],
                        'title' => $fData['title'],
                    );
                });
            }
        });

        return $response;
    }

    /**
     * Gets count of users
     *
     * @return array
     */
    public function countAction()
    {
        $query = $this->params('query');
        $query = $this->canonizeQuery($query);

        $where  = $this->canonizeCondition($query);
        $count  = Pi::service('user')->getCount($where);
        $response = array(
            'status'    => 1,
            'data'      => $count,
        );

        return $response;
    }
}