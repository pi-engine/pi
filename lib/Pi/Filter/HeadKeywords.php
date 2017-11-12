<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    protected $options = array(
        // Force lower case
        'force_lower'           => false,
        // replace space by comma(,)
        'force_replace_space'   => false,
        // Maximum count
        'max_count'             => 0,
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
        // Strip HTML tags
        $value = strip_tags($value);
        // Remove spaces
        if (!empty($this->options['force_replace_space'])) {
            $value = preg_replace('/[\s]+/', ',', trim($value));
        } else {
            $value = preg_replace('/[\s]+/', ' ', trim($value));
        }
        // Transform to lower case
        if (!empty($this->options['force_lower'])) {
            $value = strtolower($value);
        }
        $keywords = explode(',', $value);
        // Remove spaces
        $keywords = array_map('trim', $keywords);
        // Remove duplicated keywords
        $keywords = array_filter($keywords);
        if (!empty($this->options['force_lower'])) {
            $keywords = array_unique($keywords);
        } else {
            $keywords = array_intersect_key($keywords, array_unique(array_map('strtolower', $keywords)));
        }
        // Slice exceeded keywords
        if (!empty($this->options['max_count'])) {
            $keywords = array_slice($keywords, 0, $this->options['max_count']);
        }

        // Assemble head keywords string
        $value = implode(',', $keywords);

        return $value;
    }
}
