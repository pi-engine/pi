<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Controller\Api;

use Pi;
use Pi\Db\Sql\Where;
use Pi\Mvc\Controller\ActionController;

/**
 * User webservice controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaController extends ActionController
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
     * Add a doc
     *
     * @return array
     */
    public function addAction()
    {
        $query  = $this->params('query');
        $query  = $this->canonizeQuery($query);
        $row    = $this->model('doc')->createRow($query);
        $row->save();
        $response = array(
            'status'    => 1,
            'id'        => $row->id,
        );

        return $response;
    }

    /**
     * Upload a file
     */
    public function uploadAction()
    {

    }

    /**
     * Update a doc
     *
     * @return array
     */
    public function updateAction()
    {
        $id     = $this->params('id');
        $query  = $this->params('query');
        $query  = $this->canonizeQuery($query);
        $row    = $this->model('doc')->find($id);
        if (!$row) {
            $response = array(
                'status'    => 0,
                'message'   => 'Item not found.',
            );
        } else {
            $row->assign($query)->save();
            $response = array(
                'status'    => 1,
                'id'        => $row->id,
            );
        }

        return $response;
    }

    /**
     * Deletes a doc
     *
     * @return array
     */
    public function deleteAction()
    {
        $response   = array();
        $id         = $this->params('id');
        $result     = $this->model('doc')->update(
            array('time_deleted' => time(), 'active' => 0),
            array('id' => $id)
        );
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }

    /**
     * Gets a doc with specified fields
     *
     * @return array
     */
    public function getAction()
    {
        $id         = $this->params('id');
        $field      = $this->params('field');
        $fields     = $this->splitString($field);

        $select = $this->model('doc')->select()->where(array('id' => $id));
        if ($fields) {
            $select->columns($fields);
        }
        $rowset = $this->model('doc')->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row['id']] = $row->toArray();
        }
        if (isset($result[$id])) {
            $response = $result[$id];
        } else {
            $response = array();
        }

        return $response;
    }

    /**
     * Gets multiple users with specified fields
     *
     * @return array
     */
    public function mgetAction()
    {
        $id         = $this->params('id');
        $field      = $this->params('field');
        $ids        = $this->splitString($id);
        $fields     = $this->splitString($field);

        $select = $this->model('doc')->select();
        $select->where(array('id' => $ids));
        if ($fields) {
            $select->columns($fields);
        }
        $rowset = $this->model('doc')->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row['id']] = $row->toArray();
        }

        return $result;
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
        $query  = $this->canonizeQuery($query);

        $where  = $this->canonizeCondition($query);
        $select = $this->model('doc')->select();
        $select->where($where);
        $select->limit($limit);
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        $rowset = $this->model('doc')->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;
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
        $count  = $this->model('doc')->count($where);
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

    /**
     * Build query condition
     *
     * @param array $query
     *
     * @return Where
     */
    protected function canonizeCondition(array $query)
    {
        $where = array('active' => 1);
        if (isset($query['active'])) {
            $where['active'] = $query['active'];
            unset($query['active']);
        }
        $where = Pi::db()->where($where);
        if ($query) {
            foreach ($query as $qKey => $qValue) {
                $where->like($qKey, $qValue);
            }
        }

        return $where;
    }
}