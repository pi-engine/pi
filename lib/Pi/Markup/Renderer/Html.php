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
        } elseif ('text' == $this->parser) {
            if (!isset($this->options['newline'])
                || !empty($this->options['newline'])
            ) {
                $content = nl2br($content);
            }
        }
        if (!empty($this->options['tags'])) {
            $content = strip_tags($content, $this->options['tags']);
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function render($content)
    {
        $content = parent::render($content);
        if (!isset($this->options['xss_filter'])
            || !empty($this->options['xss_filter'])
        ) {
            $content = Pi::service('security')->filter($content);
        }

        return $content;
    }
}
