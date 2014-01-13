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
     * Get session instance
     * 
     * @param string  $module
     * @param string  $type
     * @return Pi\Application\Service\Session 
     */
    public static function getUploadSession($module = null, $type = 'default')
    {
        $module = $module ?: Pi::service('module')->current();
        $ns     = sprintf('%s_%s_upload', $module, $type);

        return Pi::service('session')->$ns;
    }
    
    /**
     * Get target directory
     * 
     * @param string  $section
     * @param string  $module
     * @param bool    $autoCreate
     * @param bool    $autoSplit
     * @return string 
     */
    public static function getTargetDir(
        $section, 
        $module = null, 
        $autoCreate = false, 
        $autoSplit = true
    ) {
        $module  = $module ?: Pi::service('module')->current();
        $config  = Pi::service('module')->config('', $module);
        $pathKey = sprintf('path_%s', strtolower($section));
        $path    = isset($config[$pathKey]) ? $config[$pathKey] : '';

        if ($autoSplit && !empty($config['sub_dir_pattern'])) {
            $path .= '/' . date($config['sub_dir_pattern']);
        }

        if ($autoCreate) {
            Pi::service('file')->mkdir(Pi::path($path));
        }

        return $path;
    }
    
    /**
     * Get thumb image name
     * 
     * @param string  $fileName
     * @return string 
     */
    public static function getThumbFromOriginal($fileName)
    {
        $parts = pathinfo($fileName);
        return $parts['dirname'] 
            . '/' . $parts['filename'] . '-thumb.' . $parts['extension'];
    }
    
    /**
     * Save image
     * 
     * @param array  $uploadInfo
     * @return string|bool 
     */
    public static function saveImage($uploadInfo)
    {
        $result = false;
        $size   = array();

        $fileName       = $uploadInfo['tmp_name'];
        $absoluteName   = Pi::path($fileName);
        
        $size = array($uploadInfo['w'], $uploadInfo['h']);

        Pi::service('image')->resize($absoluteName, $size);
        
        // Create thumb
        if (!empty($uploadInfo['thumb_w']) 
            or !empty($uploadInfo['thumb_h'])
        ) {
            Pi::service('image')->resize(
                $absoluteName,
                array($uploadInfo['thumb_w'], $uploadInfo['thumb_h']),
                Pi::path(self::getThumbFromOriginal($fileName))
            );
        }

        return $result ? $fileName : false;
    }
}
