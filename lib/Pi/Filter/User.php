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
        // Tag for user identity name
        'tag'           => '%user%',
        // Pattern for user identity
        'pattern'       => '@([a-zA-Z0-9]{3,32})',
        // Direct replacement for user identity: <a href="/url/to/user/%user%" title="%user%">%user%</a>
        'replacement'   => '',
        // Callback for user identity replacement if no direct replacement
        'callback'      => '',
    );

    public function __construct($options = array())
    {
        $this->setOptions($options);
        if (empty($this->options['replacement']) && empty($this->options['callback'])) {
            $this->options['callback'] = function ($identity) {
                $user = Pi::service('user')->bind($identity);
                $url = $user->getProfileUrl();
                $name = $user->getName();
                return sprintf('<a href="%s" title="%s">@%s</a>', $url, $name, $identity);
            };
        }
    }

    public function filter($value)
    {
        $replacement = $this->options['replacement'];
        if ($replacement) {
            $tag = $this->options['tag'];
            $callback = function($m) use($replacement, $tag) {
                return str_replace($tag, $m[1], $replacement);
            };
        } else {
            $func = $this->options['callback'];
            $callback = function($m) use($func) {
                return $func($m[1]);
            };
        }
        $value = preg_replace_callback('`' . $this->options['pattern'] . '`', $callback, $value);

        return $value;
    }
}
