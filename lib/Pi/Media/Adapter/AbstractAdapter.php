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
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter
{
    /** @var array Options */
    protected $options = array();

    /**
     * Constructor
     * 
     * @params array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }
    
    /**
     * Set options
     * 
     * @param array $options
     * @return  AbstractAdapter
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
        
        return $this;
    }
    
    /**
     * Get options
     * 
     * @return mixed
     */
    public function getOption()
    {
        $args = func_get_args();
        $result = $this->options;
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
     * Add a media doc
     *
     * $data attributes
     *  - appkey
     *  - module
     *  - category
     *  - path
     *  - url
     *  - title
     *  - description
     *
     *  - mimetype
     *  - filesize
     *  - size_width
     *  - size_height
     *
     *
     * @param array $data  data to update
     */
    abstract public function add($data);

    /**
     * Upload a media doc
     * 
     * @param array $meta     data written into database
     * @param array $options  optional data, use to set storage, path rule
     */
    abstract public function upload($meta, $options = array());
    
    /**
     * Update doc details
     * 
     * @param int   $id    doc ID
     * @param array $data  data to update 
     */
    abstract public function update($id, $data);
    
    /**
     * Active a doc
     * 
     * @param int   $id     file ID 
     */
    //abstract public function activeFile($id);
    abstract public function activate($id);

    /**
     * Deactivate a doc
     * 
     * @param int   $id     file ID 
     */
    //abstract public function deactivateFile($id);
    abstract public function deactivate($id);

    /**
     * Get attributes of a doc
     * 
     * @param int               $id     file ID
     * @param string|string[]   $attr  attribute key
     */
    abstract public function get($id, $attr = array());
    
    /**
     * Get attributes of files
     * 
     * @param int[]             $ids   file IDs
     * @param string|string[]   $attr  attribute key
     */
    //abstract public function mgetAttributes($ids, $attribute);
    abstract public function mget(array $ids, $attr = array());

    /**
     * Get statistics data of a file
     * 
     * @param int    $id    file ID
     */
    //abstract public function getStatistics($id, $statistics);
    abstract public function getStats($id);

    /**
     * Get statistics data of files
     * 
     * @param int[]  $ids   file IDs
     */
    //abstract public function mgetStatistics($ids, $statistics);
    abstract public function getStatsList(array $ids);

    /**
     * Get file IDs by given condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string $order 
     */
    //abstract public function getFileIds(
    abstract public function getIds(
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
