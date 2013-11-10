<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ActionController;

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
 * - checkExist: array($key => value)
 *
 * @see https://developers.google.com/admin-sdk/directory/v1/reference/users
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserController extends ActionController
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
        $result     = Pi::service('user')->get($uid, $fields);
        $response   = (array) $result;

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
        $result     = Pi::service('user')->mget($uids, $fields);
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

        $condition = array();
        if ($query) {
            $condition = Pi::db()->where();
            foreach ($query as $qKey => $qValue) {
                $condition->like($qKey, $qValue);
            }
        }
        $users = Pi::service('user')->getList(
            $condition,
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
     * @return array
     */
    public function checkExistAction()
    {
        $result = array(
            'status' => 1,
        );

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
        $condition = array();
        if ($query) {
            $condition = Pi::db()->where();
            foreach ($query as $qKey => $qValue) {
                $condition->like($qKey, $qValue);
            }
        }
        $count  = Pi::service('user')->getCount($condition);
        $response = array(
            'status'    => 1,
            'data'      => $count,
        );

        return $response;
    }

    /**
     * Split string delimited by comma `,`
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitString($string = '')
    {
        $result = array();
        if (!$string) {
            return $result;
        }

        $result = explode(',', $string);
        array_walk($result, 'trim');
        $result = array_unique(array_filter($result));

        return $result;
    }

    /**
     * Canonize query strings by convert `*` to `%` for LIKE query
     *
     * @param string $query
     *
     * @return array
     */
    protected function canonizeQuery($query = '')
    {
        $result = array();
        if (!$query) {
            return $result;
        }
        if (is_string($query)) {
            $query = $this->splitString($query);
        }
        array_walk($query, function ($qString) use (&$result) {
            list($identifier, $like) = explode(':', $qString);
            $identifier = trim($identifier);
            $like = trim($like);
            if ($identifier && $like) {
                $like = str_replace(
                    array('%', '*', '_'),
                    array('\\%', '%', '\\_'),
                    $like
                );
                $result[$identifier] = $like;
            }
        });

        return $result;
    }
}