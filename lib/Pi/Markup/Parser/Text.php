<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Parser;

use Pi;
use Laminas\Escaper\Escaper;

/**
 * Text parser
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Text extends AbstractParser
{
    /**
     * Parse a string
     *
     * @param  string $value
     * @return string
     */
    public function parseContent($value)
    {
        $encoding = empty($this->options['encoding'])
            ? Pi::service('i18n')->getCharset()
            : $this->options['encoding'];
        $escaper  = new Escaper($encoding);
        $value    = $escaper->escapeHtml($value);

        // To keep linebreak?
        if (!empty($this->options['nl2br'])) {
            $value = nl2br($value);
        }

        return $value;
    }
}
