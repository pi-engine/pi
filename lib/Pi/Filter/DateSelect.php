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
 * Date selector filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DateSelect extends AbstractFilter
{
    /**
     * {!inheritDoc}
     */
    public function filter($value)
    {
        // Convert the date to a specific format
        if (is_array($value)) {
            $date = array_filter($value);
            if ($date) {
                $date = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
            } else {
                $date = '';
            }
        } else {
            $date = $value;
        }

        return $date;
    }
}
