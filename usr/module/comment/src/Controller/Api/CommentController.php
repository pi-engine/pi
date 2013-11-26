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
 * Comment webservice controller
 *
 * Methods:
 *
 * - delete: <id>
 * - get: <id>
 * - insert: array(<field> => <value>)
 * - list: <limit>, <offset>, <order>, array(<queryKey:queryValue>)
 * - patch: <id>, array(<field> => <value>)
 * - undelete: <id>
 * - update: <id>, array(<field> => <value>)
 *
 * - mdelete: array(<id>)
 * - mget: array(<id>)
 * - mundelete: array(<id>)
 *
 * - count: array(<queryKey:queryValue>)
 * - enable: <id>
 * - disable: <id>
 *
 * - menable: array(<id>)
 * - mdisable: array(<id>)
 *
 *
 * @see https://developers.google.com/admin-sdk/directory/v1/reference/users
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CommentController extends ActionController
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
     * Deletes a post
     *
     * @return array
     */
    public function deleteAction()
    {
        return array('status' => 1);

        $response   = array();
        $id         = $this->params('id');
        $result     = Pi::service('comment')->deletePost($id);
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }

    /**
     * Gets a post
     *
     * @return array
     */
    public function getAction()
    {
        return array('status' => 1);

        $id         = $this->params('id');
        $result     = Pi::service('comment')->getPost($id);
        $response   = (array) $result;

        return $response;
    }

    /**
     * Gets multiple posts
     *
     * @return array
     */
    public function mgetAction()
    {
        return array('status' => 1);

        $id         = $this->params('id');
        $ids        = $this->splitString($id);
        $result     = Pi::service('comment')->mget($ids);
        $response   = $result;

        return $response;
    }

    /**
     * Gets a list of posts
     *
     * @return array
     */
    public function listAction()
    {
        return array('status' => 1);

        $limit  = $this->params('limit', 10);
        $offset = $this->params('offset', 0);
        $order  = $this->params('order');
        $query  = $this->params('query');

        $order  = $this->splitString($order);
        $query  = $this->canonizeQuery($query);

        $condition = array();
        if ($query) {
            $condition = Pi::db()->where();
            foreach ($query as $qKey => $qValue) {
                $condition->like($qKey, $qValue);
            }
        }
        $posts = Pi::service('comment')->getList(
            $condition,
            $limit,
            $offset,
            $order
        );

        return $posts;
    }

    /**
     * Gets count of posts
     *
     * @return array
     */
    public function countAction()
    {
        return array('status' => 1);

        $query = $this->params('query');
        $query = $this->canonizeQuery($query);
        $condition = array();
        if ($query) {
            $condition = Pi::db()->where();
            foreach ($query as $qKey => $qValue) {
                $condition->like($qKey, $qValue);
            }
        }
        $count  = Pi::service('comment')->getCount($condition);
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