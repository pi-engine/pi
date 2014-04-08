<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Api;

use Pi;

/**
 * Abstract class for content API
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractContent extends AbstractApi
{
    /** @var string Table name to fetch content data */
    protected $table;

    /** @var array Columns to fetch: table column => meta key */
    protected $meta = array(
        'id'        => 'id',
        'title'     => 'title',
        'content'   => 'content',
        'time'      => 'time',
        'uid'       => 'uid',
    );

    /**
     * Get list of item(s)
     *
     * - Meta of an item:
     *   - title
     *   - content
     *   - url (link)
     *   - time
     *   - uid
     *
     * @param string[]      $variables
     * @param array         $conditions
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
     *
     * @return array
     */
    public function getList(
        array $variables,
        array $conditions,
        $limit  = 0,
        $offset = 0,
        $order  = array()
    ) {
        $result = array();
        if (!$this->table) {
            return $result;
        }

        $variables = $this->canonizeVariables($variables);
        $conditions = $this->canonizeConditions($conditions);

        $model = Pi::model($this->table, $this->module);
        $select = $model->select();
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        $select->columns($variables);
        $select->where($conditions);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $item = $row->toArray();
            $item['url'] = $this->buildUrl($item);
            $result[] = $this->canonizeResult($item);
        }

        return $result;

    }

    /**
     * Build URL of an item
     *
     * @param array $item
     *
     * @return string
     */
    abstract protected function buildUrl(array $item);

    /**
     * Canonize result against meta
     *
     * @param array $data
     *
     * @return array
     */
    protected function canonizeResult(array $data)
    {
        $meta   = $this->meta;
        $result = array();
        foreach ($data as $var => $value) {
            if (isset($meta[$var])) {
                $result[$meta[$var]] = $value;
            }
        }
        if (isset($data['url'])) {
            $result['url']  = $data['url'];
            $result['link'] = $data['url'];
        } elseif (isset($data['link'])) {
            $result['url']  = $data['link'];
            $result['link'] = $data['link'];
        }

        return $result;
    }

    /**
     * Canonize variables against meta
     *
     * @param array $variables
     *
     * @return string[]
     */
    protected function canonizeVariables(array $variables)
    {
        $meta       = array_flip($this->meta);
        $columns    = array();
        foreach ($variables as $var) {
            if (isset($meta[$var])) {
                $columns[] = $meta[$var];
            }
        }

        return $columns;
    }

    /**
     * Canonize conditions against meta
     *
     * @param array $conditions
     *
     * @return array
     */
    protected function canonizeConditions(array $conditions)
    {
        $meta   = array_flip($this->meta);
        $result = array();
        foreach ($conditions as $var => $condition) {
            if (isset($meta[$var])) {
                $result[$meta[$var]] = $condition;
            }
        }

        return $result;
    }
}
