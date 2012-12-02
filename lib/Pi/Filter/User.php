<?php
/**
 * Pi Engine Filter Tag
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           1.0
 * @package         Pi\Filter
 * @version         $Id$
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

class User extends AbstractFilter
{
    /**
     * Filter options
     *
     * @var array
     */
    protected $options = array(
        'tag'           => '%user%',
        'pattern'       => '@([a-zA-Z0-9]{3,32})',
        'replacement'   => '<a href="pi.url/user/%user%" title="%user%">@%user%</a>',
    );

    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    public function filter($value)
    {
        $tag = $this->options['tag'];
        $replacement = $this->options['replacement'];
        $value = preg_replace_callback('`' . $this->options['pattern'] . '`', function($m) use($replacement, $tag) {
            return str_replace($tag, $m[1], $replacement);
        }, $value);

        return $value;
    }
}
