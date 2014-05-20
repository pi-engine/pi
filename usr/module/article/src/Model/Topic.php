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

/**
 * Topic model class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Topic extends Model
{
    /**
     * Get available fields
     * 
     * @return array 
     */
    public static function getAvailableFields()
    {
        return array(
            'id', 'name', 'slug', 'title',
            'template', 'description', 'image', 'content'
        );
    }

    /**
     * Get default columns
     * 
     * @return array 
     */
    public static function getDefaultColumns()
    {
        return array('id', 'slug', 'title', 'image', 'template');
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
            $columns = $this->getDefaultColumns();
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
     * Get searched row count
     * 
     * @param array  $where
     * @return int 
     */
    public function getSearchRowsCount($where = array())
    {
        $select = $this->select()
                       ->where($where)
                       ->columns(array('count' => new Expression('count(id)')));
        $count  = (int) $this->selectWith($select)->current()->count;
        
        return $count;
    }
}
