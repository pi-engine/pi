<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Parser;

use Pi;
use Zend\Escaper\Escaper;

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
        $escaper = new Escaper($encoding);
        $value = $escaper->escapeHtml($value);

        // To keep linebreak?
        if (!empty($this->options['nl2br'])) {
            $value = nl2br($value);
        }

        return $value;
    }
}
