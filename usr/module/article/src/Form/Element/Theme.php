<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Class for listing installed theme
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Theme extends Select
{
    /**
     * Read theme detail and write into options
     * 
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $rowset = Pi::model('theme')->select(array());
            $options = array();
            foreach ($rowset as $row) {
                $options[strtolower($row->name)] = ucfirst($row->name);
            }
            $this->valueOptions = array(0 => _a('Null')) + $options;
        }

        return $this->valueOptions;
    }
}
