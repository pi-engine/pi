<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller;

use Pi;
use Pi\Db\Sql\Where;

/**
 * Basic webservice API controller
 *
 * Methods:
 *
 * - delete: <id>
 * - get: <id>, array(<field>)
 * - insert: <query(queryKey:queryValue)>
 * - list: <limit>, <offset>, <order>, <query(queryKey:queryValue)>, array(<field>)
 * - patch: <id>, <query(queryKey:queryValue)>
 * - undelete: <id>
 * - update: <id>, <query<queryKey:queryValue)>
 *
 *
 * - mdelete: array(<id>)
 * - mget: array(<id>), array(<field>)
 * - mundelete: array(<id>)
 *
 * - meta
 * - count: <query<queryKey:queryValue)>
 * - activate: <id>
 * - enable: <id>
 * - disable: <id>
 *
 * - mactivate: array(<id>)
 * - menable: array(<id>)
 * - mdisable: array(<id>)
 *
 * - exist: <query<queryKey:queryValue)>
 *
 * @see    https://developers.google.com/admin-sdk/directory/v1/reference/users
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class ApiController extends ActionController
{
    /** @var string Primary model name */
    protected $modelName = '';

    /**
     * Placeholder
     *
     * @return array
     */
    public function indexAction()
    {
        return ['status' => 1];
    }

    /**
     * Add/Insert an item
     *
     * @return array
     */
    public function insertAction()
    {
        $query = $this->params('query');
        $query = $this->canonizeQuery($query);
        $row   = $this->model($this->modelName)->createRow($query);
        $row->save();
        $response = [
            'status' => 1,
            'id'     => $row->id,
        ];

        return $response;
    }

    /**
     * Update an item
     *
     * @return array
     */
    public function updateAction()
    {
        $id    = $this->params('id');
        $query = $this->params('query');
        $query = $this->canonizeQuery($query);
        $row   = $this->model($this->modelName)->find($id);
        if (!$row) {
            $response = [
                'status'  => 0,
                'message' => 'Item not found.',
            ];
        } else {
            $row->assign($query)->save();
            $response = [
                'status' => 1,
                'id'     => $row->id,
            ];
        }

        return $response;
    }

    /**
     * Gets an item with specified fields
     *
     * @return array
     */
    public function getAction()
    {
        $id     = $this->params('id');
        $field  = $this->params('field');
        $fields = $this->splitString($field);

        $model  = $this->model($this->modelName);
        $select = $model->select()->where(['id' => $id]);
        if ($fields) {
            $select->columns($fields);
        }
        $rowset = $model->selectWith($select);
        $result = [];
        foreach ($rowset as $row) {
            $result[$row['id']] = $row->toArray();
        }
        if (isset($result[$id])) {
            $response = $result[$id];
        } else {
            $response = [];
        }

        return $response;
    }

    /**
     * Gets multiple items with specified fields
     *
     * @return array
     */
    public function mgetAction()
    {
        $id     = $this->params('id');
        $field  = $this->params('field');
        $ids    = $this->splitString($id);
        $fields = $this->splitString($field);

        $model  = $this->model($this->modelName);
        $select = $model->select();
        $select->where(['id' => $ids]);
        if ($fields) {
            $select->columns($fields);
        }
        $rowset = $model->selectWith($select);
        $result = [];
        foreach ($rowset as $row) {
            $result[$row['id']] = $row->toArray();
        }

        return $result;
    }

    /**
     * Gets a list of items
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
        $model  = $this->model($this->modelName);
        $select = $model->select();
        $select->where($where);
        $select->limit($limit);
        if ($fields) {
            $select->columns($fields);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        $rowset = $model->selectWith($select);
        $result = [];
        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }

    /**
     * Gets count of items
     *
     * @return array
     */
    public function countAction()
    {
        $query    = $this->params('query');
        $query    = $this->canonizeQuery($query);
        $where    = $this->canonizeCondition($query);
        $count    = $this->model($this->modelName)->count($where);
        $response = [
            'status' => 1,
            'data'   => $count,
        ];

        return $response;
    }

    /**
     * Check if item exists against conditions
     *
     * @return array
     */
    public function existAction()
    {
        $result = [
            'status' => 1,
            'data'   => 1,
        ];

        $query = $this->params('query');
        $query = $this->canonizeQuery($query);
        if (!$query) {
            return $result;
        }
        $where = Pi::db()->where();
        foreach ($query as $key => $val) {
            $where->equalTo($key, $val)->or;
        }

        $count  = $this->model($this->modelName)->count($where);
        $result = [
            'status' => 1,
            'data'   => $count ? 1 : 0,
        ];

        return $result;
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
        $result = [];
        if (!$string) {
            return $result;
        }

        $result = explode(',', $string);
        array_walk($result, 'trim');
        $result = array_unique($result);

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
        $result = [];
        if (!$query) {
            return $result;
        }
        if (is_string($query)) {
            $query = $this->splitString($query);
        }
        array_walk(
            $query,
            function ($qString) use (&$result) {
                [$identifier, $value] = explode(':', $qString);
                $identifier = trim($identifier);
                if ($identifier) {
                    $result[$identifier] = $value;
                }
            }
        );

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
        $where = [];
        if (array_key_exists('active', $query)) {
            if (isset($query['active'])) {
                $where['active'] = $query['active'] ? 1 : 0;
            }
            unset($query['active']);
        } else {
            $where['active'] = 1;
        }
        $where = Pi::db()->where($where);
        if ($query) {
            foreach ($query as $qKey => $qValue) {
                if (false === strpos($qValue, '*')) {
                    $where->equalTo($qKey, $qValue);
                } else {
                    $qValue = str_replace(
                        ['%', '*', '_'],
                        ['\\%', '%', '\\_'],
                        $qValue
                    );
                    $where->like($qKey, $qValue);
                }
            }
        }

        return $where;
    }
}
