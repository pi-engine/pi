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
 * Category element handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Category extends CustomCommonHandler
{
    /**
     * {@inheritDoc}
     */
    public function resolve($value, $options = array())
    {
        $result = array();
        
        if (empty($value)) {
            return $result;
        }
        
        $rowset = Pi::api('category', $this->module)->getList(array(
            'id' => $value,
        ));
        if ($rowset) {
            $result = array_shift($rowset);
        }
        
        return $result;
    }
}
