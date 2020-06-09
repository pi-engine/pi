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
 * Head title text filtering
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadTitle extends AbstractFilter
{
    /**
     * Filter for head title text
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        // Strip HTML tags
        $value = trim(strip_tags($value));
        // Remove duplicated spaces and commas
        $value = preg_replace(
            ['/[\s]+/', '/[\s]?[\,]+/', '/[\,]?[\,]+/'],
            [' ', ',', ','],
            $value
        );

        return $value;
    }
}
