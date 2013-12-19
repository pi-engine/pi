<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Model;

use Pi;
use Pi\Application\Model\Model;
use Zend\Db\Sql\Expression;

/**
 * Model class for operating statistics table
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Statistics extends Model
{
    /**
     * Get all available columns
     * 
     * @return array 
     */
    public static function getAvailableColumns()
    {
        $fields = array(
            'id', 'media', 'fetch_count',
        );
        
        return $fields;
    }
    
    /**
     * Get media list
     * 
     * @return array 
     */
    public function getList(
        $condition = array(),
        $page = null,
        $limit = null,
        $columns = null,
        $order = null
    ) {
        $order = $order ?: 'media DESC';
        
        $select = $this->select();
        $select->where($condition)->order($order);
        if ($limit) {
            $offset = ((int) $page - 1) * $limit;
            $select->offset($offset)->limit($limit);
        }
        if ($columns) {
            if (!in_array('media', $columns)) {
                $columns[] = 'media';
            }
            $select->columns($columns);
        }
        
        $rowset = $this->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row->media] = $row->toArray();
        }
        
        return $result;
    }
}
