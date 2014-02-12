<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * Boolean display filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class YesNo extends AbstractFilter
{
    /**
     * Filter text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        // Convert '000<...>000' to 0
        if (is_numeric($value)) {
            $value = (int) $value;
        }
        if (!is_string($value)) {
            $value = (bool) $value ? 'yes' : 'no';
        }
        switch ($value) {
            case 'n':
            case 'no':
                $value = __('No');
                break;
            default:
                $value = __('Yes');
                break;
        }

        return $value;
    }
}
