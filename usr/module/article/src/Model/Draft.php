<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Model;

use Pi;
use Pi\Application\Model\Model;
use Zend\Db\Sql\Expression;
use Zend\Stdlib\ArrayObject;
use Module\Article\Controller\Admin\SetupController as Setup;

/**
 * Draft model
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Draft extends Model
{
    const FIELD_STATUS_DRAFT    = 1;
    const FIELD_STATUS_PENDING  = 2;
    const FIELD_STATUS_REJECTED = 3;

    protected $encodeColumns = array(
        'related'      => true,
        //'tag'          => true,
    );
    
    /**
     * Get table fields exclude id field.
     * 
     * @return array 
     */
    public function getValidColumns()
    {
        $table    = $this->getTable();
        $database = Pi::config()->load('service.database.php');
        $schema   = $database['schema'];
        $sql = 'select COLUMN_NAME as name from information_schema.columns '
             . 'where table_name=\'' 
             . $table . '\' and table_schema=\'' 
             . $schema . '\'';
        try {
            $rowset = Pi::db()->getAdapter()->query($sql, 'prepare')->execute();
        } catch (\Exception $exception) {
            return false;
        }
        
        $fields = array();
        foreach ($rowset as $row) {
            if ($row['name'] == 'id') {
                continue;
            }
            $fields[] = $row['name'];
        }
        
        return $fields;
    }

    /**
     * Get available fields
     * 
     * @param string  $module
     * @return array
     */
    public static function getAvailableFields($module = null)
    {
        $result = array(
            'id', 'article', 'subject', 'subtitle', 'image', 'author', 
            'uid', 'source', 'content', 'category', 'related', 'time_publish', 
            'time_update', 'time_submit', 'time_save', 'slug', 'seo_title',
            'seo_keywords', 'seo_description', 'status',
        );

        $module = $module ?: Pi::service('module')->current();

        if (Pi::config('enable_summary', $module)) {
            $result[] = 'summary';
        }

        if (Pi::config('enable_tag', $module)) {
            $result[] = 'tag';
        }

        return $result;
    }
    
    /**
     * Get the fields needed defines by user
     * 
     * @param string  $module  Module name
     * @return array 
     */
    public static function getValidFields($module = null)
    {
        $options         = Setup::getFormConfig();
        $availableFields = self::getAvailableFields($module);
        $remainFields    = array(
            'id', 'article', 'uid', 'time_publish', 'time_update', 'time_submit'
        );
        $validFields     = array_merge(
            $remainFields,
            array_intersect($availableFields, $options['elements'])
        );
        
        return $validFields;
    }

    /**
     * Get default column
     * 
     * @return array
     */
    public static function getDefaultColumns()
    {
        return array(
            'id', 'subject', 'subtitle', 'category', 'image', 'uid', 
            'author', 'slug', 'source', 'time_save', 'time_update', 'tag',
        );
    }

    /**
     * Create a draft
     * 
     * @return bool|int
     */
    public function createOne()
    {
        $data = array(
            'user'   => Pi::user()->getId(),
            'status' => self::FIELD_STATUS_DRAFT,
        );
        $row = $this->createRow($data);
        $row->save();

        return $row->id ?: false;
    }

    /**
     * Get draft articles by condition
     * 
     * @param array   $where
     * @param int     $limit
     * @param int     $offset
     * @param array   $columns
     * @param string  $order
     * @return array 
     */
    public function getSearchRows(
        $where = array(),
        $limit = null,
        $offset = null,
        $columns = null,
        $order = null
    ) {
        $result = $rows = array();

        $fields        = $this->getValidColumns();
        $neededColumns = empty($columns) ? self::getDefaultColumns() : $columns;
        $searchColumns = array_intersect($neededColumns, $fields);

        if (!in_array('id', $searchColumns)) {
            $searchColumns[] = 'id';
        }
        $searchColumns[] = 'detail';

        $order = (null === $order) ? 'time_save DESC' : $order;

        $select = $this->select()
            ->columns($searchColumns);

        if ($where) {
            $select->where($where);
        }

        if ($limit) {
            $select->limit(intval($limit));
        }

        if ($offset) {
            $select->offset(intval($offset));
        }

        if ($order) {
            $select->order($order);
        }

        $rows = $this->selectWith($select)->toArray();

        foreach ($rows as $row) {
            $details = json_decode($row['detail'], true);
            $result[$row['id']] = array_merge($row, $details);
            foreach (array_keys($result[$row['id']]) as $key) {
                if (!in_array($key, $neededColumns)) {
                    unset($result[$row['id']][$key]);
                }
            }
        }

        return $result;
    }

    /**
     * Get searched row count
     * 
     * @param array  $where
     * @return int
     */
    public function getSearchRowsCount($where = array())
    {
        $select = $this->select()
            ->columns(array('total' => new Expression('count(id)')));

        if ($where) {
            $select->where($where);
        }

        $resultset = $this->selectWith($select);
        $result    = intval($resultset->current()->total);

        return $result;
    }
    
    /**
     * Save a row
     * 
     * @param array  $data
     * @return ArrayObject 
     */
    public function saveRow($data)
    {
        $columns = $this->getValidColumns();
        $details = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                continue;
            }
            $details[$key] = $value;
            unset($data[$key]);
        }
        $data['detail'] = json_encode($details);
        
        $row = $this->createRow($data);
        $row->save();
        
        return $row;
    }
    
    /**
     * Update draft row.
     * 
     * @param array  $data
     * @param array  $where 
     * @return bool
     */
    public function updateRow($data, $where)
    {
        foreach (array_keys($where) as $key) {
            unset($data[$key]);
        }
        
        $columns = $this->getValidColumns();
        $details = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                continue;
            }
            $details[$key] = $value;
            unset($data[$key]);
        }
        $data['detail'] = json_encode($details);
        
        $result = $this->update($data, $where);
        
        return $result;
    }
    
    /**
     * Find a article
     * 
     * @param string  $value
     * @param string  $key
     * @param bool    $arrayOrObject
     * @return array|object 
     */
    public function findRow($value, $key = 'id', $arrayOrObject = true)
    {
        $row = $this->find($value, $key);
        if (!$row->id) {
            return $row;
        }
        $details = json_decode($row->detail, true) ?: array();
        $row     = array_merge($row->toArray(), $details);
        unset($row['detail']);
        
        return $arrayOrObject ? $row : (object) $row;
    }
}
