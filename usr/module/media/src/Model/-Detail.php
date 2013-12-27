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
//use Zend\Db\Sql\Expression;

/**
 * Model class for operating application table
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Detail extends Model
{
    /**
     * Remove unneeded fields
     * 
     * @param array $data
     * @return array 
     */
    protected function canonize($data)
    {
        $fields = static::getAvailableColumns();
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $fields)
                || null === $data[$key]
            ) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
    
    /**
     * Get default columns
     * 
     * @return array 
     */
    public static function getDefaultColumns()
    {
        $fields = array(
            'id', 'title', 'raw_name', 'mimetype', 'uid', 'url', 'filesize', 
            'size_width', 'size_height', 'module', 'application', 'time_upload',
            'category',
        );
        
        return $fields;
    }
    
    /**
     * Get all available columns
     * 
     * @return array 
     */
    public static function getAvailableColumns()
    {
        $fields = array(
            'id', 'name', 'title', 'raw_name', 'mimetype', 'description',
            'uid', 'url', 'filesize', 'size_width', 'size_height', 'ip',
            'module', 'application', 'active', 'time_upload', 'category'
        );
        
        return $fields;
    }
    
    /**
     * Get meta columns
     * 
     * @return array 
     */
    public static function getMetaColumns()
    {
        $fields = array();
        
        return $fields;
    }

    /**
     * Get media list
     *
     * @param array $condition
     * @param null  $page
     * @param null  $limit
     * @param null  $columns
     * @param null  $order
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
        trigger_error(__METHOD__ . ': use `offset` instead of `page`; moreover, do not encapsulate business logic into database model, move to APIs if possible.');

        $order = $order ?: 'time_upload DESC';
        
        $select = $this->select();
        $select->where($condition)->order($order);
        if ($limit) {
            $offset = ((int) $page - 1) * $limit;
            $select->offset($offset)->limit($limit);
        }
        if ($columns) {
            $select->columns($columns);
        }
        
        $rowset = $this->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row->id] = $row->toArray();
        }
        
        return $result;
    }
    
    /**
     * Get list count by condition
     * 
     * @param array  $condition
     * @return int
     */
    public function getCount(array $condition = array())
    {
        trigger_error(__METHOD__ . ': No need to add this method.');
        /*
        $select = $this->select()->where((array) $condition);
        $select->columns(array('count' => new Expression('count(*)')));
        $rowset = $this->selectWith($select);
        
        $count = intval($rowset->current()->count);
        */
        $count = $this->count($condition);
        
        return $count;
    }
    
    /**
     * Save media details
     * 
     * @param array $data
     * @param array $options
     * @return Model
     */
    public function saveData($data, $options = array())
    {
        $values = $this->canonize($data);
        
        // Canonize meta data
        $metaFields = static::getMetaColumns();
        $meta = array();
        foreach ($data as $key => $val) {
            if (in_array($key, $metaFields)) {
                $meta[$key] = $val;
            }
        }
        foreach ($options as $key => $val) {
            if (in_array($key, $metaFields)) {
                $meta[$key] = $val;
            }
        }
        if (!empty($meta)) {
            $values['meta'] = json_encode($meta);
        }
        
        // Upload time
        if (!isset($values['time_upload'])) {
            $values['time_upload'] = time();
        }
        
        $row = $this->createRow($values);
        $row->save();
        
        return $row;
    }

    /**
     * Update media title, description and url
     *
     * @param array $data
     * @param array $where
     *
     * @return bool
     */
    public function updateData(array $data, array $where)
    {
        $columns = array('title', 'name', 'description', 'url');
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }
        
        $data['time_update'] = time();
        $result = $this->update($data, $where);
        
        return $result;
    }
    
    /**
     * Active or deactivate media
     * 
     * @param int $id
     * @param int $status
     * @return boolean
     */
    public function active($id, $status = 1)
    {
        $status = $status ? 1 : 0;
        
        $result = $this->update(
            array('active' => $status),
            array('id' => $id)
        );
        
        return $result;
    }
}
