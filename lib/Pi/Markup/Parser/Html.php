<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Parser;

/**
 * HTML content parser
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Html extends AbstractParser
{
    /**
     * Parse a string
     *
     * @param  string $value
     * @return string
     */
    public function parseContent($value)
    {
        if (!empty($this->options['safe_tags'])) {
            $value = strip_tags($value, $this->options['safe_tags']);
        }

        return $value;
    }
}
