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
 * Gender display filter
 *
 * Transform gender specs:
 *
 * - `male`     => `__('Male')`
 * - `female`   => `__('Female')`
 * - `unknown`  => `__('Unknown gender')`
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Gender extends AbstractFilter
{
    /**
     * Filter text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        switch ($value) {
            case 'm':
            case 'male':
                $value = __('Male');
                break;
            case 'f':
            case 'female':
                $value = __('Female');
                break;
            case 'u':
            case 'unknown':
            default:
                $value = __('Unknown gender');
                break;
        }

        return $value;
    }
}
