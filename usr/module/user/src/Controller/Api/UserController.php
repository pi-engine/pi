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
 * - list: <limit>, <offset>, <order>, array(<queryKey> => <queryValue>), array(<field>)
 * - patch: <id>, array(<field> => <value>)
 * - undelete: <id>
 * - update: <id>, array(<field> => <value>)
 *
 *
 * - mdelete: array(<id>)
 * - mget: array(<id>), array(<field>)
 * - mundelete: array(<id>)
 *
 * - activate: <id>
 * - enable: <id>
 * - disable: <id>
 *
 * - mactivate: array(<id>)
 * - menable: array(<id>)
 * - mdisable: array(<id>)
 *
 * @see https://developers.google.com/admin-sdk/directory/v1/reference/users
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserController extends ActionController
{
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
        $uid    = $this->params('id');
        $field  = $this->params('field');

        $fields = $field ? explode(',', $field) : array();
        $result = Pi::service('user')->get($uid, $fields);
        $response = (array) $result;

        return $response;
    }

    /**
     * Gets multiple users with specified fields
     *
     * @return array
     */
    public function mgetAction()
    {
        $uid    = $this->params('id');
        $field  = $this->params('field');

        $uids   = explode(',', $uid);
        $fields = $field ? explode(',', $field) : array();
        $result = Pi::service('user')->get($uids, $fields);
        $response = (array) $result;

        return $response;
    }

    /**
     * Gets a list of users
     *
     * @return array
     */
    public function listAction()
    {
        $users = array();

        $limit = $this->params('limit', 10);
        $offset = $this->params('offset', 0);
        $order = $this->params('order');
        $query = $this->params('query');
        $field = $this->params('field');

        $order = $order ? explode(',', $order) : array();
        $query = $query ? explode(',', $query) : array();
        $fields = $field ? explode(',', $field) : array();

        $condition = array();
        if ($query) {
            $condition = Pi::db()->where();
            foreach ($query as $qString) {
                list($identifier, $like) = explode(':', $qString);
                $condition->like($identifier, $like);
            }
        }
        $count  = Pi::service('user')->getCount($condition);
        if ($count) {
            $uids   = Pi::service('user')->getUids(
                $condition,
                $limit,
                $offset,
                $order
            );
            $users  = Pi::service('user')->get($uids, $fields);
        }

        $response = array(
            'count' => $count,
            'users' => $users,
        );

        return $response;
    }

    /**
     * Placeholder for TODOs
     *
     * @param string $method
     * @param array  $args
     *
     * @return array|mixed
     */
    public function __call($method, $args = array())
    {
        $response = array(
            'status'    => 0,
            'message'   => sprintf('The API %s is not implemented yet.', $method),
        );

        return $response;
    }
}