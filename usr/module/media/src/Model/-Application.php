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
 * Model class for operating application table
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Application extends Model
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
        return array('id', 'appkey', 'name', 'title');
    }

    /**
     * Get application title
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
    public function saveData(array $data)
    {
        $row = $this->find($data['appkey'], 'appkey');
        if (!$row) {
            $data = $this->canonize($data);
            $row = $this->createRow($data);
            $row->save();
        } else {
            // No update?
        }
        
        return $row;
    }
}
