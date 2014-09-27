<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Field;

use Pi;

/**
 * Gallery element handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Gallery extends CustomCommonHandler
{
    /**
     * Asset type
     * @var string 
     */
    protected $type = 'image';
    
    /**
     * {@inheritDoc}
     */
    public function getModel()
    {
        return Pi::model('asset', $this->module);
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($id, $filter = false)
    {
        $where  = array(
            'article' => $id,
            'type'    => $this->type,
        );
        $rowset = $this->getModel()->select($where);
        
        return $rowset->toArray();
    }
    
    /**
     * Insert asset into table
     * 
     * @param int  $id
     * @param array|string|int $data
     * @return bool
     */
    public function add($id, $data)
    {
        if (is_string($data) && !is_numeric($data)) {
            $data = explode(',', $data);
        }
        $data  = (array) $data;
        
        $table = $this->getModel()->getTable();
        $sql   = "INSERT INTO `{$table}` (`article`, `media`, `type`) VALUES ";
        foreach ($data as $media) {
            $sql .=<<<EOD
($id, $media, '{$this->type}'),
EOD;
        }
        $sql = rtrim($sql, ',') . ';';
        
        try {
            Pi::db()->getAdapter()->query($sql, 'prepare')->execute();
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $where = array(
            'article' => $id,
            'type'    => $this->type,
        );
        return $this->getModel()->delete($where);
    }
    
    /**
     * {@inheritDoc}
     */
    public function resolve($value, $options = array())
    {
        $result = array();
        
        $mediaIds = array_filter(explode(',', $value));
        if (empty($mediaIds)) {
            return array();
        }
        
        $rowset = Pi::model('media', $this->module)->select(
            array('id' => $mediaIds)
        );
        foreach ($rowset as $row) {
            $result[$row->id] = array(
                'original_name' => $row->title,
                'extension'     => $row->type,
                'size'          => $row->size,
                'url'           => Pi::service('url')->assemble('default', array(
                    'module'       => $this->module,
                    'controller'   => 'media',
                    'action'       => 'download',
                    'name'         => $row->id,
                )),
            );
        }
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function encode($id)
    {
        $data = $this->get($id);
        
        $mediaIds = array();
        array_walk($data, function($value) use (&$mediaIds) {
            $mediaIds[] = $value['media'];
        });
        
        return array($this->name => implode(',', $mediaIds));
    }
}
