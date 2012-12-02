<?php
/**
 * Pi Engine Markup Parser
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
 * @package         Pi\Markup
 * @version         $Id$
 */

namespace Pi\Markup\Parser;

use MarkdownDocument;

class Markdown extends AbstractParser
{
    /**
     * Parse a string
     *
     * @param  string $value
     *
     * @return array
     */
    public function parse($value)
    {
        if (!class_exists('MarkdownDocument')) {
            return $value;
        }
        $markdown = MarkdownDocument::createFromString($value);
        $markdown->compile();
        $value = $markdown->getHtml();

        return $value;
    }
}
