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
}
