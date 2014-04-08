<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        if (!$this->parser || 'text' == $this->parser) {
            $content = Pi::service('security')->escape($content);
        } else {
            if ($this->parser instanceof AbstractParser) {
                $content = $this->parser->parse($content);
            }
            $content = strip_tags($content);
        }
        if (!empty($this->options['newline'])) {
            $content = nl2br($content);
        }

        return $content;
    }
}
