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
 * Stats model class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Stats extends Model
{
    /**
     * Get avaliable columns
     * 
     * @return array 
     */
    public static function getAvailableColumns()
    {
        $columns = array('visits');
        
        return $columns;
    }
    
    /**
     * Increase visit count of a article.
     *
     * @param int  $id  Article ID
     * @return array
     */
    public function increaseVisits($id)
    {
        $row = $this->find($id, 'article');
        if (empty($row)) {
            $data = array(
                'article'  => $id,
                'visits'   => 1,
            );
            $row    = $this->createRow($data);
            $result = $row->save();
        } else {
            $row->visits++;
            $result = $row->save();
        }

        return $result;
    }
    
    /**
     * Get list
     * 
     * @param array       $where
     * @param int|null    $offset
     * @param int|null    $limit
     * @param array|null  $columns
     * @param string|null $order
     * @return array 
     */
    public function getList(
        $where = array(),
        $offset = null,
        $limit = null,
        $columns = null,
        $order = null
    ) {
        if (!empty($limit)) {
            $offset = $offset ?: 0;
        }
        
        $select = $this->select()->where($where);
        
        if ($offset !== null) {
            $select->offset($offset);
        }
        
        if (!empty($limit)) {
            $select->limit($limit);
        }
        
        $columns = $columns ?: self::getAvailableColumns();
        if (!in_array('article', $columns)) {
            $columns[] = 'article';
        }
        $select->columns($columns);
        
        $order = $order ?: 'id DESC';
        $select->order($order);
        
        $resultSet = $this->selectWith($select);
        $rows      = array();
        foreach ($resultSet as $set) {
            $rows[$set->article] = $set->toArray();
        }
        
        return $rows;
    }
}
