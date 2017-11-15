<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Renderer;

use Pi;
use Pi\Markup\Parser\AbstractParser;

/**
 * Render to text format
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Text extends AbstractRenderer
{
    /**
     * {@inheritDoc}
     */
    protected function renderContent($content)
    {
        $content = strip_tags($content);

        return $content;
    }
}
