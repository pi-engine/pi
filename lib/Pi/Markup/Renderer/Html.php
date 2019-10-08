<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Renderer;

/**
 * Render to HTML format
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Html extends AbstractRenderer
{
    /**
     * {@inheritDoc}
     */
    protected function renderContent($content)
    {
        return $content;
    }
}
