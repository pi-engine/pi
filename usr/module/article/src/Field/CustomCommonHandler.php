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
 * Custom common field handling
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CustomCommonHandler extends CommonHandler
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
        $rowset = $this->getModel()->select(array('article' => $id));
        
        return $rowset->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function mget($ids, $filter = false)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function display($id, $data = null) {}
    
    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        return $this->getModel()->delete(array('article' => $id));
    }
}
