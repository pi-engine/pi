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
 * Tag filter
 *
 * Transiliate specified format of text into tag links:
 * From `#term#` to `<a href="<tag-link>/tag/term" title="term">#term#</a>`
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tag extends AbstractFilter
{
    /**
     * Filter options
     * @var array
     */
    protected $options = array(
        'tag'           => '%tag%',
        'pattern'       => '#([^\s\,]{3,32})#',
        'replacement'   =>
            '<a href="pi.url/tag/%tag%" title="%tag%">#%tag%#</a>',
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Filter text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $tag = $this->options['tag'];
        $replacement = $this->options['replacement'];
        $value = preg_replace_callback(
            '`' . $this->options['pattern'] . '`',
            function ($m) use ($replacement, $tag) {
                return str_replace($tag, $m[1], $replacement);
            },
            $value
        );

        return $value;
    }
}
