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
 * Head meta keywords text filtering
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadKeywords extends AbstractFilter
{
    /** @var array */
    protected $optioins = array(
        // Force lower case
        'force_lower'   => false,
        // Maximum count
        'max_count'     => 0,
    );

    /**
     * Filter for keywords text
     *
     * @param string|array $value
     *
     * @return string
     */
    public function filter($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        // Strip HTML tags and remove unrecognizable characters
        $value =trim(_strip($value));
        // Transform multi-spaces/commas to single comma
        $value = preg_replace('/[\,]+[\s]?/', ',', $value);
        // Transform to lower case
        if (!empty($this->options['force_lower'])) {
            $value = strtolower($value);
        }
        // Remove duplicated keywords
        $keywords = array_unique(array_filter(explode(',', $value)));
        // Slice extra keywords
        if (!empty($this->options['max_count'])) {
            $keywords = array_slice($keywords, 0, $this->options['max_count']);
        }

        // Assemble head keywords string
        $value = implode(',', $keywords);

        return $value;
    }
}
