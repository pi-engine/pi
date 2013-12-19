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
 * Local media service provided by media module
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Local extends AbstractAdapter
{
    /**
     * Get server module name
     * 
     * @return string 
     */
    protected function getServer()
    {
        return isset($this->options['server']) 
            ? $this->options['server'] : 'media';
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
        $server = $this->getServer();
        $result = Pi::api($server, 'media')->upload($meta, $options);
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
        $server = $this->getServer();
        return Pi::api($server, 'media')->update($id, $data);
    }
    
    /**
     * Active a file
     * 
     * @param int  $id
     * @return boolean 
     */
    public function activeFile($id)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->activeFile($id);
    }
    
    /**
     * Deactivate a file
     * 
     * @param int  $id
     * @return boolean 
     */
    public function deactivateFile($id)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->deactivateFile($id);
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
        $server = $this->getServer();
        return Pi::api($server, 'media')->getAttributes($id, $attribute);
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
        $server = $this->getServer();
        return Pi::api($server, 'media')->mgetAttributes($ids, $attribute);
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
        $server = $this->getServer();
        return Pi::api($server, 'media')->getStatistics($id, $statistics);
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
        $server = $this->getServer();
        return Pi::api($server, 'media')->mgetStatistics($ids, $statistics);
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
        $server = $this->getServer();
        $result = Pi::api($server, 'media')->getFileIds(
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
        $server = $this->getServer();
        $result = Pi::api($server, 'media')->getList(
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
        $server = $this->getServer();
        return Pi::api($server, 'media')->getCount($condition);
    }
    
    /**
     * Get file url
     * 
     * @param int $id 
     * @return string
     */
    public function getUrl($id)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getUrl($id);
    }
    
    /**
     * Get url of files
     * 
     * @param array $ids 
     * @return array
     */
    public function mgetUrl($ids)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->mgetUrl($ids);
    }
    
    /**
     * Download files
     * 
     * @param array $ids 
     */
    public function download($ids)
    {
        $server = $this->getServer();
        Pi::api($server, 'media')->download($ids);
    }
    
    /**
     * Delete files
     * 
     * @param array $ids 
     * @return boolean
     */
    public function delete($ids)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->delete($ids);
    }
    
    /**
     * Get file validator data
     * 
     * @param string $adapter 
     * @return array
     */
    public function getValidator($adapter = null)
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getValidator($adapter);
    }
    
    /**
     * Get server configuration
     * 
     * @return array 
     */
    public function getServerConfig()
    {
        $server = $this->getServer();
        return Pi::api($server, 'media')->getServerConfig();
    }
}
