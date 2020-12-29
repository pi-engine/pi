<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Laminas\Filter\AbstractFilter;

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
     *
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
