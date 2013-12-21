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
use Pi\Media\Dispatch\AbstractDispatch as MediaDispatch;

/**
 * Media service abstract class
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
abstract class AbstractAdapter
{
    /**
     * Options
     * @var array 
     */
    protected $options = array();
    
    /**
     * Adapter configs
     * 
     * @var array 
     */
    protected $configs;
    
    /**
     * Dispatch handler
     * 
     * @var MediaDispatch
     */
    protected $dispatch;
    
    /**
     * Adapter configuration file
     * @var string 
     */
    protected $configFile = 'service.media.php';
    
    /**
     * Constructor
     * 
     * @params array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
        $this->setConfigs();
        $this->setDispatch();
    }
    
    /**
     * Set options
     * 
     * @param array $options
     * @return \Pi\Media\Adapter\AbstractAdapter 
     */
    public function setOptions($options = array())
    {
        $this->options = $options;
        
        return $this;
    }
    
    /**
     * Get options
     * 
     * @return array 
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * Set adapter configs
     * 
     * @return \Pi\Media\Adapter\AbstractAdapter 
     */
    public function setConfigs()
    {
        $this->configs = Pi::service('config')->load($this->configFile);
        
        return $this;
    }
    
    /**
     * Get adapter configs
     * 
     * @return array|mixed 
     */
    public function getConfig()
    {
        if (null == $this->configs) {
            $this->setConfigs();
        }
        $args = func_get_args();
        $result = $this->configs;
        foreach ($args as $name) {
            if (is_array($result) && isset($result[$name])) {
                $result = $result[$name];
            } else {
                $result = null;
                break;
            }
        }
        
        return $result;
    }

    /**
     * Initialize dispatch handler
     *
     * @param array $configs
     *
     * @throws \Exception
     * @return $this
     */
    public function setDispatch(array $configs = array())
    {
        $configs = !empty($configs) ? $configs : $this->getConfig('dispatch');
        $dispatch = $configs['name'];
        $configs = isset($configs['configs']) ? $configs['configs'] : array();
        $options = isset($configs['options']) ? $configs['options'] : array();
        if (empty($dispatch)) {
            $dispatch = 'local';
            $configs = array();
            $options = array();
        }
        $class = sprintf('Pi\Media\Dispatch\\%s', ucfirst($dispatch));
        if (!class_exists($class)) {
            $message = sprintf('Class %s not exists', $class);
            throw new \Exception($message);
        }
        $this->dispatch = new $class($configs, $options);
        
        return $this;
    }
    
    /**
     * Get dispatch handler
     * 
     * @return Pi\Media\Dispatch\AbstractDispatch
     */
    public function getDispatch()
    {
        if (empty($this->dispatch)) {
            $this->dispatch = $this->setDispatch();
        }
        
        return $this->dispatch;
    }
    
    /**
     * Upload a file
     * 
     * @param array $meta     data written into database
     * @param array $options  optional data, use to set storage, path rule
     */
    abstract public function upload($meta, $options = array());
    
    /**
     * Update file details
     * 
     * @param int   $id    file ID
     * @param array $data  data to update 
     */
    abstract public function update($id, $data);
    
    /**
     * Active a file
     * 
     * @param int   $id     file ID 
     */
    //abstract public function activeFile($id);
    abstract public function activate($id);

    /**
     * Deactivate a file
     * 
     * @param int   $id     file ID 
     */
    //abstract public function deactivateFile($id);
    abstract public function deactivate($id);

    /**
     * Get attributes of a file
     * 
     * @param int   $id     file ID
     * @param string $attribute  attribute key 
     */
    abstract public function getAttributes($id, $attribute);
    
    /**
     * Get attributes of files
     * 
     * @param int[]  $ids   file IDs
     * @param string $attribute  attribute key 
     */
    //abstract public function mgetAttributes($ids, $attribute);
    abstract public function getAttributesList(array $ids, $attribute);

    /**
     * Get statistics data of a file
     * 
     * @param int    $id    file ID
     * @param string $statistics  key  
     */
    //abstract public function getStatistics($id, $statistics);
    abstract public function getStats($id, $statistics);

    /**
     * Get statistics data of files
     * 
     * @param int[]  $ids   file IDs
     * @param string $statistics  key  
     */
    //abstract public function mgetStatistics($ids, $statistics);
    abstract public function getStatsList(array $ids, $statistics);

    /**
     * Get file IDs by given condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order 
     */
    abstract public function getFileIds(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    );
    
    /**
     * Get list by condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order 
     */
    abstract public function getList(
        array $condition,
        $limit = null,
        $offset = null,
        $order = null
    );
    
    /**
     * Get list count by condition
     * 
     * @param array $condition 
     */
    abstract public function getCount(array $condition = array());
    
    /**
     * Get file url
     * 
     * @param int|int[] $id
     */
    abstract public function getUrl($id);
    
    /**
     * Get url of files
     * 
     * @param int[] $ids
     */
    //abstract public function mgetUrl($ids);
    abstract public function getUrlList(array $ids);

    /**
     * Download files
     * 
     * @param int[] $ids
     */
    abstract public function download(array $ids);
    
    /**
     * Delete files
     * 
     * @param array $ids 
     */
    abstract public function delete(array $ids);
    
    /**
     * Get file validator data
     * 
     * @param string $adapter 
     */
    abstract public function getValidator($adapter = null);
    
    /**
     * Get configuration of server 
     */
    abstract public function getServerConfig();
}
