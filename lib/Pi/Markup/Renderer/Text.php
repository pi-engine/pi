<?php
/**
 * Pi Engine Markup Renderer
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

namespace Pi\Markup\Renderer;

use Pi;
use Pi\Markup\Parser\AbstractParser;
//use Pi\Security\Security;

class Text extends AbstractRenderer
{
    /**
     * Parse content
     *
     * @param string $content
     *
     * @return string
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
        if (!isset($this->options['newline']) || !empty($this->options['newline'])) {
            $content = nl2br($content);
        }

        return $content;
    }
}
