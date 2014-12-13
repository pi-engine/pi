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
 * Slug text filtering
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Slug extends AbstractFilter
{
    /** @var array */
    protected $options = array(
        // Force lower case
        'force_lower'   => false,
    );

    /**
     * Filter for slug text
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        // Strip HTML tags and remove unrecognizable characters
        $value =trim(_strip($value));
        // Transform to lower case
        if (!empty($this->options['force_lower'])) {
            $value = strtolower($value);
        }
        // Transform multi-spaces to slash
        $value = preg_replace('/[\s]+/', '-', $value);

        return $value;
    }
}
