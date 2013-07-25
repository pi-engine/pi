<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Markup\Parser;

use MarkdownDocument;

/**
 * Markdown parser
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Markdown extends AbstractParser
{
    /**
     * Parse a string
     *
     * @param  string $value
     * @return string
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
