<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Model;

use Pi\Application\Model\Model as BasicModel;

/**
 * Article field model
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Field extends BasicModel
{
    /**
     * Columns to be encoded
     *
     * @var array
     */
    protected $encodeColumns = array(
        'edit'      => true,
        'filter'    => true,
    );
    
    /**
     * Table columns
     * 
     * @var array 
     */
    protected $columns = array();
    
    /**
     * Get table columns
     * 
     * @param bool $fetch
     * @return array
     */
    public function getColumns($fetch = false)
    {
        if (empty($this->columns)) {
            $this->columns = parent::getColumns(true);
        }
        
        return $this->columns;
    }
    
    /**
     * Remove un-exists columns
     * 
     * @param array $data
     * @return array
     */
    public function canonizeColumns($data)
    {
        $columns = $this->getColumns();
        
        foreach (array_keys($data) as $field) {
            if (!in_array($field, $columns)) {
                unset($data[$field]);
            }
        }
        
        return $data;
    }
}
