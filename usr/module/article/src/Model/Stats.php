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
     * Get table fields exclude id field.
     * 
     * @return array 
     */
    public function getColumns($fetch = false)
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
     * Increase visit count of a article.
     *
     * @param int  $id  Article ID
     * @return array
     */
    public function increase($id)
    {
        // Increase date count
        $rowDate = $this->select(array('article' => $id));
        $dateId  = array();
        foreach ($rowDate as $row) {
            $dateId[$row->date] = $row->id;
        }
        
        $time = time();
        
        // Increase today count
        if (isset($dateId['D'])) {
            $rowDay = $this->find($dateId['D']);
            $date   = date('Ymd', $rowDay->time_updated);
            if (date('Ymd', time()) != $date) {
                $rowDay->visits = 1;
            } else {
                $rowDay->visits += 1;
            }
            $rowDay->time_updated = $time;
            $rowDay->save();
        } else {
            $data = array(
                'article'      => $id,
                'date'         => 'D',
                'visits'       => 1,
                'time_updated' => $time,
            );
            $rowDay = $this->createRow($data);
            $rowDay->save();
        }
        
        // Increase this week count
        if (isset($dateId['W'])) {
            $rowWeek = $this->find($dateId['W']);
            $date   = date('W', $rowWeek->time_updated);
            if (date('W', time()) != $date) {
                $rowWeek->visits = 1;
            } else {
                $rowWeek->visits += 1;
            }
            $rowWeek->time_updated = $time;
            $rowWeek->save();
        } else {
            $data = array(
                'article'      => $id,
                'date'         => 'W',
                'visits'       => 1,
                'time_updated' => $time,
            );
            $rowWeek = $this->createRow($data);
            $rowWeek->save();
        }
        
        // Increase this month count
        if (isset($dateId['M'])) {
            $rowMonth = $this->find($dateId['M']);
            $date   = date('Ym', $rowMonth->time_updated);
            if (date('Ym', time()) != $date) {
                $rowMonth->visits = 1;
            } else {
                $rowMonth->visits += 1;
            }
            $rowMonth->time_updated = $time;
            $rowMonth->save();
        } else {
            $data = array(
                'article'      => $id,
                'date'         => 'M',
                'visits'       => 1,
                'time_updated' => $time,
            );
            $rowMonth = $this->createRow($data);
            $rowMonth->save();
        }
        
        // Increase date 
        if (isset($dateId['A'])) {
            $rowAll = $this->find($dateId['A']);
            $rowAll->visits += 1;
            $rowAll->time_updated = $time;
            $rowAll->save();
        } else {
            $data = array(
                'article'      => $id,
                'date'         => 'A',
                'visits'       => 1,
                'time_updated' => $time,
            );
            $rowAll->time_updated = $time;
            $rowAll = $this->createRow($data);
            $rowAll->save();
        }

        return true;
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
        
        $columns = $columns ?: $this->getColumns(true);
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
