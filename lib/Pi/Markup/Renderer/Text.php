<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Markup\Renderer;

use Pi;
use Pi\Markup\Parser\AbstractParser;

/**
 * Text renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Text extends AbstractRenderer
{
    /**
     * {@inheritDoc}
     */
    protected function parse($content)
    {
        if ($this->parser instanceof AbstractParser) {
            $content = $this->parser->parse($content);
            $content = Pi::service('security')->escape($content);
        } elseif ('html' == $this->parser) {
            $content = strip_tags($content);
        } else {
            $content = Pi::service('security')->escape($content);
        }
        if (!isset($this->options['newline'])
            || !empty($this->options['newline'])
        ) {
            $content = nl2br($content);
        }

        return $content;
    }
}
