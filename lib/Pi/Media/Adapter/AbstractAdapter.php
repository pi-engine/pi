<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Media\Adapter;

use Pi;
use Pi\Media\Dispatch\AbstractDispatch as MediaDispatch;

/**
 * Media service abstract adapter
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
     *  - type
     *  - path
     *  - url
     *  - title
     *  - description
     *  - attributes
     *  - uid
     *  - id
     *  - ...
     *
     *
     * @param array $data Doc attribute data
     *
     * @return int Doc id
     */
    abstract public function add(array $data);

    /**
     * Upload a local media doc
     * 
     * @param string|array|Resource $file
     * @param array $data Doc attribute data
     *
     * @return int Doc id
     */
    abstract public function upload($file, array $data = array());

    /**
     * Download a doc file
     *
     * @param int|int[] $id   Doc id
     *
     * @return bool
     */
    abstract public function download($id);
    
    /**
     * Update doc details
     * 
     * @param int   $id    doc ID
     * @param array $data  data to update
     *
     * @return bool
     */
    abstract public function update($id, array $data);
    
    /**
     * Activate/deactivate a doc
     * 
     * @param int $id     Doc ID
     * @param bool $flag
     *
     * @return bool
     */
    abstract public function activate($id, $flag = true);

    /**
     * Get attributes of a doc
     * 
     * @param int               $id    Doc ID
     * @param string|string[]   $attr  attribute key
     *
     * @return mixed
     */
    abstract public function get($id, $attr = array());
    
    /**
     * Get attributes of files
     * 
     * @param int[]             $ids   Doc IDs
     * @param string|string[]   $attr  attribute key
     *
     * @return array
     */
    abstract public function mget(array $ids, $attr = array());

    /**
     * Get doc file url
     *
     * @param int|int[] $id
     *
     * @return string|array
     */
    abstract public function getUrl($id);

    /**
     * Get statistics data of a file
     * 
     * @param int    $id    Doc ID
     *
     * @return array
     */
    abstract public function getStats($id);

    /**
     * Get statistics data of files
     * 
     * @param int[]  $ids   Doc IDs
     *
     * @return array
     */
    abstract public function getStatsList(array $ids);

    /**
     * Get file IDs by given condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string|array $order
     *
     * @return int[]
     */
    abstract public function getIds(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = ''
    );
    
    /**
     * Get list by condition
     * 
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string|array $order
     * @param array $attr
     *
     * @return array
     */
    abstract public function getList(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = '',
        array $attr = array()
    );
    
    /**
     * Get doc count by condition
     * 
     * @param array $condition
     *
     * @return int
     */
    abstract public function getCount(array $condition = array());

    /**
     * Delete file(s)
     * 
     * @param int|int[] $id
     */
    abstract public function delete($id);
}
