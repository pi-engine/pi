<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Media\Adapter;

use Pi;

/**
 * Remote media service provided by media module
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Module extends AbstractAdapter
{
    /**
     * Current module
     * @var string 
     */
    protected $module;
    
    /**
     * Get current module name
     * @return string 
     */
    protected function getModule()
    {
        if (!$this->module) {
            $this->module = Pi::service('module')->current();
        }
        
        return $this->module;
    }
    
    /**
     * Upload a file
     * 
     * @param array $meta     data written into database
     * @param array $options  optional data, use to set storage, path rule
     * @return array
     * @throws \InvalidArgumentException 
     */
    public function upload($meta, $options = array())
    {
        $module = $this->getModule();
        $result = Pi::api($module, 'media')->upload($meta, $options);
        if ($result['absolute_path']) {
            $dispatch = $this->getDispatch();
            if ($dispatch->copy($meta['source'], $result['absolute_path'])) {
                $result['url'] = $result['relative_path'];
                if (!$this->update($result['id'], $result)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Update file details
     * 
     * @param int   $id
     * @param array $data
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    public function update($id, $data)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->update($id, $data);
    }
    
    /**
     * Active a file
     * 
     * @param int  $id
     * @return boolean 
     */
    public function activeFile($id)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->activeFile($id);
    }
    
    /**
     * Deactivate a file
     * 
     * @param int  $id
     * @return boolean 
     */
    public function deactivateFile($id)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->deactivateFile($id);
    }
    
    /**
     * Get attributes of a file
     * 
     * @param int     $id
     * @param string  $attribute
     * @return array|boolean 
     */
    public function getAttributes($id, $attribute)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getAttributes($id, $attribute);
    }
    
    /**
     * Get attributes of files
     * 
     * @param array  $ids   file IDs
     * @param string $attribute  attribute key 
     * @return array
     */
    public function mgetAttributes($ids, $attribute)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->mgetAttributes($ids, $attribute);
    }
    
    /**
     * Get statistics data of a file
     * 
     * @param int    $id    file ID
     * @param string $statistics  key
     * @return array
     */
    public function getStatistics($id, $statistics)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getStatistics($id, $statistics);
    }
    
    /**
     * Get statistics data of files
     * 
     * @param array  $ids   file IDs
     * @param string $statistics  key
     * @return array
     */
    public function mgetStatistics($ids, $statistics)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->mgetStatistics($ids, $statistics);
    }
    
    /**
     * Get file IDs by given condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order
     * @return array
     */
    public function getFileIds(
        $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        $module = $this->getModule();
        $result = Pi::api($module, 'media')->getFileIds(
            $condition,
            $limit,
            $offset,
            $order
        );
        
        return $result;
    }
    
    /**
     * Get list by condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order 
     * @return array
     */
    public function getList(
        $condition,
        $limit = null,
        $offset = null,
        $order = null
    ) {
        $module = $this->getModule();
        $result = Pi::api($module, 'media')->getList(
            $condition,
            $limit,
            $offset,
            $order
        );
        
        return $result;
    }
    
    /**
     * Get list count by condition
     * 
     * @param array $condition 
     * @return int
     */
    public function getCount($condition = array())
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getCount($condition);
    }
    
    /**
     * Get file url
     * 
     * @param int $id 
     * @return string
     */
    public function getUrl($id)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getUrl($id);
    }
    
    /**
     * Get url of files
     * 
     * @param array $ids 
     * @return array
     */
    public function mgetUrl($ids)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->mgetUrl($ids);
    }
    
    /**
     * Download files
     * 
     * @param array $ids 
     */
    public function download($ids)
    {
        $module = $this->getModule();
        Pi::api($module, 'media')->download($ids);
    }
    
    /**
     * Delete files
     * 
     * @param array $ids 
     * @return boolean
     */
    public function delete($ids)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->delete($ids);
    }
    
    /**
     * Get file validator data
     * 
     * @param string $adapter 
     * @return array
     */
    public function getValidator($adapter = null)
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getValidator($adapter);
    }
    
    /**
     * Get server configuration
     * 
     * @return array
     */
    public function getServerConfig()
    {
        $module = $this->getModule();
        return Pi::api($module, 'media')->getServerConfig();
    }
}
