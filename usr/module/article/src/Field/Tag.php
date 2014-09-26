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
 * Tag element handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Tag extends CustomCommonHandler
{
    /**
     * Insert tag into tag module
     * 
     * @param int    $id
     * @param string $data
     * @return bool
     */
    public function add($id, $data)
    {
        $result = Pi::service('tag')->add(
            $this->module,
            $id,
            '',
            $data
        );
        
        return $result;
    }
}
