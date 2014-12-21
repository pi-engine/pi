<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Head description text filtering
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadDescription extends AbstractFilter
{
    /**
     * Filter for head description text
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        // Strip HTML tags
        $value =trim(strip_tags($value));
        // Remove duplicated spaces, commas
        $value = preg_replace(
            array('/[\s]+/', '/[\s]?[\,]+/', '/[\,]?[\,]+/'),
            array(' ', ',', ','),
            $value
        );

        return $value;
    }
}
