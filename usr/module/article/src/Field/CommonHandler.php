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
 * Common field handling
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CommonHandler extends AbstractCommonHandler
{
    /**
     * Tanslate current data into format that can be display in detail page
     * 
     * @param mixed $value
     * @param array $options  Optional data
     * @return mixed
     */
    public function resolve($value, $options = array())
    {
        return $value;
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($id, $filter = false)
    {
        $row = $this->getModel()->find($id);
        
        $result = $row->$this->name;
        if ($filter) {
            $result = $this->resolve($result);
        }
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget($ids, $filter = false)
    {
        $select = $this->getModel()->columns(array('id', $this->name))
            ->where(array('id' => $ids));
        $rowset = $this->getModel()->selectWith($select);
        
        $result = array();
        foreach ($rowset as $row) {
            $value = $row->$this->name;
            if ($filter) {
                $value = $this->resolve($value);
            }
            $result[$row->id] = $value;
        }
        
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function display($id, $data = null)
    {
        
    }
}
