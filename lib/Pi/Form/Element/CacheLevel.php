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
 *  Cache level select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CacheLevel extends Select
{
    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = array(
                'none'      => __('None'),
                'locale'    => __('Locale'),
                'role'      => __('Role'),
                'user'      => __('User'),
            );
        }

        return $this->valueOptions;
    }
}
