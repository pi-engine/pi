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

/**
 * Topic model class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Topic extends Model
{
    /**
     * Get table fields exclude id field.
     * 
     * @return array 
     */
    public function getColumns($fetch = false, $default = false)
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
            if ($default
                && in_array($row['name'], array('content', 'description'))
            ) {
                continue;
            }
            $fields[] = $row['name'];
        }
        
        return $fields;
    }

    /**
     * Change topic slug to ID
     * 
     * @param string  $slug  Topic unique flag
     * @return int 
     */
    public function slugToId($slug)
    {
        $result = false;

        if ($slug) {
            $row = $this->find($slug, 'slug');
            if ($row) {
                $result = $row->id;
            }
        }

        return $result;
    }
    
    /**
     * Set active status
     * 
     * @param int|string  $id      Topic ID or slug
     * @param int         $status  Status
     * @return bool 
     */
    public function setActiveStatus($id, $status = 1)
    {
        if (is_numeric($id)) {
            $row = $this->find($id);
        } else {
            $row = $this->find($id, 'slug');
        }
        
        $row->active = $status;
        $result = $row->save();
        
        return $result;
    }
    
    /**
     * Get topic list
     * 
     * @param array       $where
     * @param array|null  $columns
     * @param bool        $all      To fetch all details or only title
     * @return array 
     */
    public function getList($where = array(), $columns = null, $all = false)
    {
        if (empty($columns)) {
            $columns = $this->getColumns(true, true);
        }
        if (!isset($columns['id'])) {
            $columns[] = 'id';
        }
        
        $select = $this->select()
                       ->where($where)
                       ->columns($columns);
        $rowset = $this->selectWith($select);
        
        $list = array();
        foreach ($rowset as $row) {
            if ($all) {
                $list[$row->id] = $row->toArray();
            } else {
                $list[$row->id] = $row->title;
            }
        }
        
        return $list;
    }
    
    /**
     * Remove un-exist columns
     * 
     * @param array $data
     * @return mixed
     */
    public function canonizeColumns(&$data)
    {
        $data    = (array) $data;
        $columns = $this->getColumns(true);
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }
    }
}
