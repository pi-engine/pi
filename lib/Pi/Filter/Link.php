<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * Link filter
 *
 * Transliterate a URL to clickable link:
 * From `http://url.tld` to `<a href="http://url.tld" title="Click to open">http://url.tld</a>`
 *
 * @see http://stackoverflow.com/questions/5341168/best-way-to-make-links-clickable-in-block-of-text
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Link extends AbstractFilter
{
    /**
     * Filter text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $value = preg_replace(
            '!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i',
            '<a href="$1" title="' . __('Click to open') . '">$1</a>',
            $value
        );

        return $value;
    }
}
