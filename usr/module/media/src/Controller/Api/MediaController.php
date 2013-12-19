<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Media\Service;

/**
 * User webservice controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaController extends ActionController
{
    /**
     * Add media detail into database
     * 
     * @return array 
     */
    public function uploadAction()
    {
        $meta    = $this->params('meta');
        $meta    = json_decode($meta, true);
        $options = $this->params('options');
        $options = json_decode($options, true);
        
        // Save application data
        $row = $this->getModel('application')->saveData(array(
            'appkey'    => $meta['appkey'],
            'name'      => $meta['application'],
        ));
        if (!$row) {
            return array(
                'status'    => false,
                'message'   => __('Cannot save application data'),
            );
        }
        $meta['application'] = $row->id;
        unset($meta['appkey']);
        unset($row);
        
        // Save category
        $row = $this->getModel('category')->saveData(array(
            'module' => $meta['module'],
            'name'   => isset($meta['category']) ? $meta['category'] : '',
            'title'  => isset($meta['category_title']) 
                ? $meta['category_title'] : '',
        ));
        if (!$row) {
            return false;
        }
        $meta['category'] = $row->id;
        unset($row);
        
        // Save meta data
        $row = $this->getModel('detail')->saveData($meta, $options);
        if (!$row) {
            return array(
                'status'    => false,
                'message'   => __('Cannot save media data'),
            );
        }
        $meta['id'] = $row->id;
        
        // Get media filename and file path
        if (isset($options['upload'])) {
            
        } else {
            $file = Service::getMediaFile($meta);
            $filename = $file['filename'];
            $path = $file['path'];
        }
        $name = substr($filename, 0, strrpos($filename, '.'));
        
        $result = array(
            'name'          => $name,
            'relative_path' => $path,
            'absolute_path' => Pi::path($path),
            'target_url'    => Pi::url($path),
        );
        
        return array_merge($row->toArray(), $result);
    }
    
    /**
     * Process uploaded media
     * 
     * @return array 
     */
    public function curlAction()
    {
        $result = false;
        
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            
            // @TODO: Authorization
            
            // Get file
            $target = $data['target'];
            Service::mkdir(dirname($target));
            $file = $this->request->getFiles();
            $result = move_uploaded_file($file['file']['tmp_name'], $target);
        }
        
        return array(
            'status' => $result,
        );
    }
    
    /**
     * Update media details
     * 
     * @return array 
     */
    public function updateAction()
    {
        $id = $this->params('id');
        if (empty($id)) {
            return array(
                'status'    => false,
                'message'   => __('Missing file ID'),
            );
        }
        $where = array('id' => $id);
        
        $data = $this->params('data');
        $data = json_decode($data, true);
        
        $model = $this->getModel('detail');
        $result = $model->updateData($data, $where);
        
        return array('status' => $result);
    }
    
    /**
     * Get attributes of given media id
     * 
     * @return array 
     */
    public function getAttributesAction()
    {
        $id = $this->params('id');
        $where   = array('id' => $id);
        
        $columns = $this->params('attribute');
        $columns = explode(',', $columns);
        
        $model = $this->getModel('detail');
        $result = $model->getList($where, null, null, $columns);
        $result = array_shift($result);
        
        return $result;
    }
    
    /**
     * Get attributes of given media ids
     * 
     * @return array 
     */
    public function mgetAttributesAction()
    {
        $id = $this->params('id');
        $id = explode(',', $id);
        $where   = array('id' => $id);
        
        $columns = $this->params('attribute');
        $columns = explode(',', $columns);
        
        $model = $this->getModel('detail');
        $result = $model->getList($where, null, null, $columns);
        
        return $result;
    }
    
    /**
     * Get statistics of media
     * 
     * @return array 
     */
    public function getStatisticsAction()
    {
        $id = $this->params('id');
        $id = explode(',', $id);
        $where   = array('media' => $id);
        
        $columns = $this->params('statistics');
        $columns = explode(',', $columns);
        
        $model = $this->getModel('statistics');
        $result = $model->getList($where, null, null, $columns);
        $result = array_shift($result);
        
        return $result;
    }
    
    /**
     * Get statistics of given medias
     * 
     * @return array 
     */
    public function mgetStatisticsAction()
    {
        $id = $this->params('id');
        $id = explode(',', $id);
        $where   = array('media' => $id);
        
        $columns = $this->params('statistics');
        $columns = explode(',', $columns);
        
        $model = $this->getModel('statistics');
        $result = $model->getList($where, null, null, $columns);
        
        return $result;
    }
    
    /**
     * Get media IDs by condition
     * 
     * @return array 
     */
    public function getIdsAction()
    {
        $where  = $this->params('query');
        $where  = json_decode($where, true);
        $limit  = $this->params('limit', null);
        $offset = $this->params('offset', null);
        $order  = $this->params('order', null);
        $page   = ceil($offset / $limit) + 1;
        
        $rowset = $this->getModel('detail')
            ->getList($where, $page, $limit, null, $order);
        $result = array_keys($rowset);
        
        return $result;
    }
    
    /**
     * Get media list by condition
     * 
     * @return array 
     */
    public function getListAction()
    {
        $where  = $this->params('query');
        $where  = json_decode($where, true);
        $limit  = $this->params('limit', null);
        $offset = $this->params('offset', null);
        $order  = $this->params('order', null);
        $page   = ceil($offset / $limit) + 1;
        
        $result = $this->getModel('detail')
            ->getList($where, $page, $limit, null, $order);
        
        return $result;
    }
    
    /**
     * Get count by condition
     * 
     * @return array 
     */
    public function getCountAction()
    {
        $where  = $this->params('query');
        $where  = json_decode($where, true);
        
        $result = $this->getModel('detail')->getCount($where);
        
        return array($result);
    }
    
    /**
     * Get url of media
     * 
     * @return array 
     */
    public function getUrlAction()
    {
        $id = $this->params('id');
        $where   = array('id' => $id);
        
        $columns = array('id', 'url');
        $result = $this->getModel('detail')
            ->getList($where, null, null, $columns);
        $result = array_shift($result);
        $result = $result ? Pi::url($result) : '';
        
        return array($result['url']);
    }
    
    /**
     * Get url of medias
     * 
     * @return array 
     */
    public function mgetUrlAction()
    {
        $ids = $this->params('id');
        $where   = array('id' => explode(',', $ids));
        
        $columns = array('id', 'url');
        $rowset = $this->getModel('detail')
            ->getList($where, null, null, $columns);
        foreach ($rowset as &$row) {
            if (!empty($row['url'])) {
                $row['url'] = Pi::url($row['url']);
            } else {
                $row['url'] = '';
            }
        }
        
        return $rowset;
    }
}
