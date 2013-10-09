<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article;

use Pi;

/**
 * Media service API
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Media
{
    protected static $module = 'article';
    
    /**
     * Get media list
     * 
     * @param array        $where
     * @param int|null     $page
     * @param int|null     $limit
     * @param array|null   $columns
     * @param string|null  $order
     * @param string|null  $module
     * @return array 
     */
    public static function getList(
        $where = array(), 
        $page = null, 
        $limit = null, 
        $columns = null, 
        $order = null, 
        $module = null
    ) {
        $module = $module ?: Pi::service('module')->current();
        $model  = Pi::model('media', $module);
        
        // Assebling select
        $select = $model->select()->where($where);
        
        if ($page and $limit) {
            $offset = intval($limit) * (intval($page) - 1);
            $select->offset($offset)->limit($limit);
        }
        
        if ($columns) {
            $select->columns($columns);
        }
        
        $order  = $order ?: 'time_upload DESC';
        $select->order($order);
        
        // Fetching data
        $rowset   = $model->selectWith($select);
        $mediaSet = array();
        $mediaIds = array(0);
        $submitterIds = array(0);
        foreach ($rowset as $row) {
            $item               = $row->toArray();
            $item['size']       = self::transferSize($item['size']);
            $item['type']       = strtolower($item['type']);
            $meta = empty($row->meta) ? array() : json_decode($row->meta, true);
            unset($item['meta']);
            $item['previewUrl'] = Pi::engine()->application()
                                              ->getRouter()
                                              ->assemble(array(
                                                  'module'     => $module,
                                                  'controller' => 'media',
                                                  'action'     => 'detail',
                                                  'id'         => $row->id,
                                              ), array('name' => 'default'));
            $item = array_merge($item, $meta);
            $mediaSet[$row->id] = $item;
            $mediaIds[]         = $row->id;
            $submitterIds[]     = $row->uid;
        }
        
        // Fetching statistics data
        $model  = Pi::model('media_statistics', $module);
        $rowset = $model->select(array('media' => $mediaIds));
        foreach ($rowset as $row) {
            $id = $row['media'];
            $statistics = $row->toArray();
            unset($statistics['id']);
            unset($statistics['media']);
            $mediaSet[$id] = array_merge($mediaSet[$id], $statistics);
        }
        
        // Fetching submitter
        $model  = Pi::model('user_account');
        $rowset = $model->select(array('id' => $submitterIds));
        $submitter = array();
        foreach ($rowset as $row) {
            $submitter[$row->id] = $row->name;
        }
        
        foreach ($mediaSet as &$set) {
            $set['submitter'] = isset($submitter[$set['uid']]) ? $submitter[$set['uid']] : '';
            $set['url']       = Pi::url($set['url']);
        }
        
        return $mediaSet;
    }
    
    /**
     * Transfer size
     * 
     * @param string|int   $value
     * @param bool         $direction  
     * @return boolean 
     */
    public static function transferSize($value, $direction = true)
    {
        if (!is_string($value) and !is_numeric($value)) {
            return false;
        }
        
        $result = $value;
        if ($direction) {
            if (!is_numeric($value)) {
                return $value;
            }
            $value = intval($value);
            if ($value / (1024 * 1024 * 1024 * 1024) > 1) {
                $result = sprintf('%.2f', $value / (1024 * 1024 * 1024 * 1024)) . 'T';
            } elseif ($value / (1024 * 1024 * 1024) > 1) {
                $result = sprintf('%.2f', $value / (1024 * 1024 * 1024)) . 'G';
            } elseif ($value / (1024 * 1024) > 1) {
                $result = sprintf('%.2f', $value / (1024 * 1024)) . 'M';
            } elseif ($value / 1024 > 1) {
                $result = sprintf('%.2f', $value / 1024) . 'K';
            } else {
                $result = $value . 'B';
            }
        } else {
            $value  = trim($value);
            if (is_numeric($value)) {
                return $value;
            }
            if (preg_match('/^\d\d*[a-zA-Z]$/', $value)) {
                $unit   = substr($value, strlen($value) - 1);
                $number = substr($value, 0, strlen($value) - 1);
            } elseif (preg_match('/^\d\d*[a-zA-Z]{2}$/', $value)) {
                $unit   = substr($value, strlen($value) - 2);
                $number = substr($value, 0, strlen($value) - 2);
            } else {
                return false;
            }
            switch (strtolower($unit)) {
                case 't':
                case 'tb':
                    $result = $number * 1024 * 1024 * 1024 * 1024;
                    break;
                case 'g':
                case 'gb':
                    $result = $number * 1024 * 1024 * 1024;
                    break;
                case 'm':
                case 'mb':
                    $result = $number * 1024 * 1024;
                    break;
                case 'k':
                case 'kb':
                    $result = $number * 1024;
                    break;
                default:
                    $result = false;
                    break;
            }
        }
        
        return $result;
    }
}
