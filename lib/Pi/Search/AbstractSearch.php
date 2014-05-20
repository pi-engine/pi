<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Search;

use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\Db\Sql\Where;
use Pi\Application\Model\Model;

/**
 * Abstract class for module search
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractSearch extends AbstractApi
{
    /** @var string Table name */
    protected $table;

    /** @var array columns to search */
    protected $searchIn = array(
        'title',
        'content'
    );

    /** @var array Columns to fetch: table column => meta key */
    protected $meta = array(
        'id'        => 'id',
        'title'     => 'title',
        'content'   => 'content',
        'time'      => 'time',
        'uid'       => 'uid',
    );

    /** @var array Extra conditions */
    protected $condition = array(
        'active'    => 1,
    );

    /** @var array Search order */
    protected $order = array(
        'id DESC'
    );

    /**
     * Search query
     *
     * @param array|string  $terms
     * @param int           $limit
     * @param int           $offset
     * @param array         $condition
     *
     * @return ResultSet
     */
    public function query(
        $terms,
        $limit  = 0,
        $offset = 0,
        array $condition = array()
    ) {
        $terms = (array) $terms;
        $model = $this->getModel();
        $where = $this->buildCondition($terms, $condition);
        $count = $model->count($where);
        $data = array();
        if ($count) {
            $data = $this->fetchResult($model, $where, $limit, $offset);
        }
        $result = $this->buildResult($count, $data);

        return $result;
    }

    /**
     * Get table model
     *
     * @return Model
     */
    protected function getModel()
    {
        $model = Pi::model($this->table, $this->module);

        return $model;
    }

    /**
     * Build query condition
     *
     * @param array $terms
     * @param array $condition
     *
     * @return Where
     */
    protected function buildCondition(array $terms, array $condition = array())
    {
        $where = Pi::db()->where()->or;
        // Create search term clause
        foreach ($terms as $term) {
            foreach ($this->searchIn as $column) {
                $where->like($column, '%' . $term . '%')->or;
            }
        }
        // Canonize conditions
        if ($condition) {
            $meta = array_flip($this->meta);
            foreach (array_keys($condition) as $key) {
                if (isset($meta[$key])) {
                    $condition[$meta[$key]] = $condition[$key];
                    unset($condition[$key]);
                }
            }
            $condition = array_merge($this->condition, $condition);
        } else {
            $condition = $this->condition;
        }
        // Create condition clauses
        if ($condition) {
            $where = Pi::db()->where($where);
            $where->add($condition);
        }

        return $where;
    }

    /**
     * Fetch search result count
     *
     * @param Model $model
     * @param Where $where
     *
     * @return int
     */
    protected function fetchCount(Model $model, Where $where)
    {
        $count = $model->count($where);

        return $count;
    }

    /**
     * Fetch search result
     *
     * @param Model $model
     * @param Where $where
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    protected function fetchResult(
        Model $model,
        Where $where,
        $limit = 0,
        $offset = 0
    ) {
        $data = array();
        $select = $model->select();
        $select->where($where);
        $select->columns(array_keys($this->meta));
        $select->limit($limit)->offset($offset);
        if ($this->order) {
            $select->order($this->order);
        }
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $item = array();
            foreach ($this->meta as $column => $field) {
                $item[$field] = $row[$column];
                if ('content' == $field) {
                    $item[$field] = $this->buildContent($item[$field]);
                }
            }
            $item['url'] = $this->buildUrl($item);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * Formulate content for render
     *
     * @param string $content
     *
     * @return string
     */
    protected function buildContent($content = '')
    {
        $content = mb_substr(strip_tags($content), 0, 255);

        return $content;
    }

    /**
     * Build item link URL
     *
     * @param array $item
     *
     * @return string
     */
    protected function buildUrl(array $item)
    {
        return Pi::url('www');
    }

    /**
     * Build search result set
     *
     * @param int   $total
     * @param array $data
     *
     * @return ResultSet
     */
    public function buildResult($total, array $data)
    {
        $result = new ResultSet($total, $data);

        return $result;
    }
}
