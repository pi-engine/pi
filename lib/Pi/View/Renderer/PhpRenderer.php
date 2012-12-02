<?php
/**
 * PhpRenderer class
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
 * @since           3.0
 * @package         Pi\View
 * @version         $Id$
 */

namespace Pi\View\Renderer;

use Zend\View\Renderer\PhpRenderer as ZendPhpRenderer;
use Zend\View\Model\ModelInterface as Model;

class PhpRenderer extends ZendPhpRenderer
{
    /**
     * Processes a view script and returns the output.
     *
     * @param  string|Model $nameOrModel Either the template to use, or a
     *                                   ViewModel. The ViewModel must have the
     *                                   template as an option in order to be
     *                                   valid.
     * @param  null|array|Traversable Values to use when rendering. If none
     *                                provided, uses those in the composed
     *                                variables container.
     * @return string The script output.
     */
    public function __render($nameOrModel, $values = null)
    {
        $cacheEvent = null;

        if ($nameOrModel instanceof Model) {
            /**#@++
             * Load content from cache
             */
            $options = $nameOrModel->getOptions();
            $cacheEvent = isset($options['cache']) ? $options['cache'] : null;
            if ($cacheEvent && $cacheEvent->isCached()) {
                return $cacheEvent->cachedContent();
            }
            /**#@-*/
        }
        $content = parent::render($nameOrModel, $values);
        if ($cacheEvent && $cacheEvent->cachable()) {
            $cacheEvent->setContent($content)->saveCache();
        }
        return $content;
    }
}
