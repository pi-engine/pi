<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     * @param string $value
     *
     * @return string
     */
    public function parseContent($value)
    {
        if (!class_exists('MarkdownDocument')) {
            $value = nl2br($value);
            return $value;
        }
        $markdown = MarkdownDocument::createFromString($value);
        $markdown->compile();
        $value = $markdown->getHtml();

        return $value;
    }
}
