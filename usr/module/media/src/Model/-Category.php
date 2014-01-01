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

/**
 * Model class for operating category table
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Category extends Model
{
    /**
     * Remove unneeded fields
     * 
     * @param array $data
     * @return array 
     */
    protected function canonize($data)
    {
        $fields = static::getAvailableFields();
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $fields)) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
    
    /**
     * Get available fields
     * 
     * @return array 
     */
    public static function getAvailableFields()
    {
        return array('id', 'module', 'name', 'title', 'active');
    }

    /**
     * Get category title
     *
     * @param int[] $ids
     *
     * @return array
     */
    public function getTitle(array $ids)
    {
        trigger_error(__METHOD__ . ': set the value when it is created; do not add this method.');

        $rowset = $this->select(array('id' => $ids));
        $result = array();
        foreach ($rowset as $row) {
            $result[$row->id] = $row->title ?: $row->name;
        }

        return $result;
    }
    
    /**
     * Save data into database
     * 
     * @param array $data
     * @return array 
     */
    public function saveData($data)
    {
        $data['name'] = $data['name'] ?: 'default';
        $rowset = $this->select(array(
            'module'    => $data['module'],
            'name'      => $data['name'],
        ));
        
        if (count($rowset)) {
            $row = $rowset->current();
            // No update?
        } else {
            $data['title'] = $data['title'] ?: ucfirst($data['name']);
            $data = $this->canonize($data);
            $row = $this->createRow($data);
            $row->save();
        }
        
        return $row;
    }
}
