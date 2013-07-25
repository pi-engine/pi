<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Markup\Renderer;

use Pi\Markup\Parser\AbstractParser;

/**
 * HTML renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Html extends AbstractRenderer
{
    /**
     * {@inheritDoc}
     */
    protected function parse($content)
    {
        if ($this->parser instanceof AbstractParser) {
            $content = $this->parser->parse($content);
        }
        if (!empty($this->options['tags'])) {
            $content = strip_tags($content, $this->options['tags']);
        }

        return $content;
    }
}
