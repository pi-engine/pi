<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Api;

use Pi;
use Pi\Application\AbstractApi;
use Module\Media\Service;

class Media extends AbstractApi
{
    /**
     * Module name
     * @var string
     */
    protected $module = 'media';

    /**
     * Get model
     *
     * @param string $name
     *
     * @return Pi\Application\Model\Model
     */
    protected function model($name = 'doc')
    {
        $model = Pi::Model($name, $this->module);

        return $model;
    }

    /**
     * Add an application
     *
     * @param $data
     *
     * @return mixed
     */
    public function addApplication($data)
    {
        $model  =$this->model('application');
        $row = $model->find($data['appkey'], 'appkey');
        if (!$row) {
            $row = $model->createRow($data);
            $row->save();
        }

        return $row->id;
    }

    /**
     * Insert media details
     * 
     * @param array $meta
     * @param array $options
     * @return bool
     */
    public function upload($meta, $options = array())
    {
        $module = $this->getModule();
        
        // Save application data
        $row = Pi::model('application', $module)->saveData(array(
            'appkey'    => $meta['appkey'],
            'name'      => $meta['application'],
        ));
        if (!$row) {
            return false;
        }
        $meta['application'] = $row->id;
        unset($meta['appkey']);
        unset($row);
        
        // Save category
        $row = Pi::model('category', $module)->saveData(array(
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
        $row = Pi::model('detail', $module)->saveData($meta, $options);
        if (!$row) {
            return false;
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
     * Update media details
     * 
     * @param array $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        if (empty($id)) {
            return false;
        }
        
        $where = array('id' => $id);
        
        $model = Pi::model('detail', $this->getModule());
        $result = $model->updateData($data, $where);
        
        return $result;
    }
    
    /**
     * Active media
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        $model = Pi::model('detail', $this->getModule());
        $result = $model->active($id);
        
        return $result ? true : false;
    }
    
    /**
     * Deactivate file
     * 
     * @param int $id
     * @return bool
     */
    public function deactivate($id)
    {
        $model = Pi::model('detail', $this->getModule());
        $result = $model->active($id, 0);
        
        return $result ? true : false;
    }
    
    /**
     * Get media attributes
     * 
     * @param int   $id
     * @param string|string[] $attribute
     * @return array
     */
    public function getAttributes($id, $attribute = array())
    {
        $id = (array) $id;
        if (count($id) > 1) {
            return false;
        }
        
        $where   = array('id' => $id);
        $model = Pi::model('detail', $this->getModule());
        $result = $model->getList($where, null, null, (array) $attribute);
        $result = array_shift($result);
        
        return $result;
    }
    
    /**
     * Get attributes of medias
     * 
     * @param array $ids
     * @param array $attribute
     * @return array
     */
    public function getAttributesList(array $ids, $attribute)
    {
        $where = array('id' => $ids);
        $model = Pi::model('detail', $this->getModule());
        $result = $model->getList($where, null, null, (array) $attribute);

        return $result;
    }
    
    /**
     * Get media statistics
     * 
     * @param int   $id
     * @param array $statistics
     * @return array
     */
    public function getStats($id, $statistics)
    {
        $id = (array) $id;
        if (count($id) > 1) {
            return false;
        }
        $where = array('media' => $id);
        
        $model = Pi::model('statistics', $this->getModule());
        $result = $model->getList($where, null, null, (array) $statistics);
        $result = array_shift($result);
        
        return $result;
    }
    
    /**
     * Get statistics of medias
     * 
     * @param int[] $ids
     * @param array $statistics
     * @return array
     */
    public function getStatsList(array $ids, $statistics)
    {
        $ids = (array) $ids;
        $where = array('media' => $ids);
        
        $model = Pi::model('statistics', $this->getModule());
        $result = $model->getList($where, null, null, (array) $statistics);

        return $result;
    }
    
    /**
     * Get media ID by condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order
     * @return array
     */
    public function getFileIds(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        if (empty($condition)) {
            $condition = array();
        }
        $order = $order ?: 'id ASC';
        $page  = ceil($offset / $limit) + 1;
        $model = Pi::model('detail', $this->getModule());
        $rowset = $model->getList($condition, $page, $limit, null, $order);
        $result = array_keys($rowset);
        
        return $result;
    }
    
    /**
     * Get media list
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order
     * @return array
     */
    public function getList(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        if (empty($condition)) {
            $condition = array();
        }
        $order = $order ?: 'id ASC';
        $page  = ceil($offset / $limit) + 1;
        $model = Pi::model('detail', $this->getModule());
        $result = $model->getList($condition, $page, $limit, null, $order);
        
        return $result;
    }
    
    /**
     * Get media count by condition
     * 
     * @param array $condition
     * @return int
     */
    public function getCount(array $condition = array())
    {
        if (empty($condition)) {
            $condition = array();
        }

        $model = Pi::model('detail', $this->getModule());
        $result = $model->getCount($condition);
        
        return $result;
    }
    
    /**
     * Get media url
     * 
     * @param int $id
     * @return string
     */
    public function getUrl($id)
    {
        $model = Pi::model('detail', $this->getModule());
        $row   = $model->find($id);
        
        if (!$row) {
            return '';
        } elseif (empty($row->url)) {
            return '';
        } else {
            return Pi::url($row->url);
        }
    }
    
    /**
     * Get url of medias
     * 
     * @param int[] $ids
     * @return array
     */
    public function getUrlList(array $ids)
    {
        $model = Pi::model('detail', $this->getModule());
        $ids = (array) $ids;
        $rowset = $model->select(array('id' => $ids));
        $urls   = array();
        foreach ($rowset as $row) {
            if (empty($row->url)) {
                $urls[$row->id] = '';
            } else {
                $urls[$row->id] = Pi::url($row->url);
            }
        }
        
        return $urls;
    }
    
    /**
     * Download medias
     * 
     * @param int[] $ids
     * @throws \Exception
     */
    public function download(array $ids)
    {
        $module = $this->getModule();
        
        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID!'));
        }
        
        $url = Pi::service('url')->assemble('default', array(
            'module'     => $module,
            'controller' => 'download',
            'action'     => 'index',
            'id'         => implode(',', $ids),
        ));
        
        $location = sprintf('location: %s', $url);
        header($location);
    }
    
    /**
     * Delete medias
     * 
     * @param int[] $ids
     * @return bool
     */
    public function delete(array $ids)
    {
        $model = Pi::model('detail', $this->getModule());
        $ids = (array) $ids;
        $result = $model->delete(array('id' => $ids));
        
        return $result;
    }
}
