<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Navigation select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Navigation extends Select
{
    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $rowset = Pi::model('navigation')->select(array(
                'section'   => 'front',
                'active'    => 1,
            ));

            foreach($rowset as $row) {
                $this->valueOptions[$row->name] = $row->title;
            }
        }

        return $this->valueOptions;
    }
}
