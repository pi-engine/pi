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
 * @see https://bitbucket.org/kwi/urllinker/src
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Link extends AbstractFilter
{
    /** @var array */
    protected $options = array(
        // attributes
        'attributes'    => array(),
        // open in new window
        'open_new'      => true,
    );

    /**
     * Filter text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $attributes = array();
        if (!empty($this->options['attributes'])) {
            $attributes = $this->options['attributes'];
        }
        if (!isset($attributes['target']) && !empty($this->options['open_new'])) {
            $attributes['target'] = '_blank';
        }
        if (!isset($attributes['title'])) {
            $attributes['title'] = __('Click to open');
        }
        $helper = Pi::service('view')->getHelper('html_link');

        $pattern = '!((((f|ht)tp(s)?:)?//|www\.)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i';
        $link = preg_replace_callback($pattern, function ($matches) use ($attributes, $helper) {
            $url = $matches[1];
            if ('www.' == $matches[2]) {
                $href = 'http://' . $url;
            } else {
                $href = $url;
            }
            $link = $helper($href, $url, $attributes);

            return $link;
        }, $value);

        return $link;
    }
}
