<?php
/**
 * Theme template resolver
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

namespace Pi\View\Resolver;

use Pi;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 * Theme template resolver
 * @see \Pi\View\Resolver\ModuleTemplate for module template skeleton
 * @see \Pi\View\Resolver\ComponentTemplate for component template skeleton
 * @see \Pi\Application\Service\Asset for asset skeleton
 *
 * Theme template folders/files skeleton:
 *  <code>theme/default/template/</code>
 */

/**
 * Resolves theme view scripts
 *
 * @see Zend\View\Resolver\ResolverInterface
 */
class ThemeTemplate implements ResolverInterface
{
    /**
     * Theme template diretory
     * @var type
     */
    protected $templateDirectory = 'template';

    /**
     * Suffix to use
     *
     * Appends this suffix if the template requested does not use it.
     *
     * @var string
     */
    protected $suffix = 'phtml';

    /**
     * Set default file suffix
     *
     * @param  string $suffix
     * @return ThemeTemplate
     */
    public function setSuffix($suffix)
    {
        $this->suffix = (string) $suffix;
        return $this;
    }

    /**
     * Get file suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Canonize template
     *
     * @param string $name
     * @param string $theme
     * @return string
     */
    protected function canonizeTemplate($name, $theme = null)
    {
        // Trim suffix
        if (substr($name, -6) == '.' . $this->suffix) {
            $name = substr($name, 0, -6);
        }
        $theme = $theme ?: Pi::service('theme')->current();
        $template = sprintf('%s/%s/%s/%s.%s', Pi::path('theme'), $theme, $this->templateDirectory, $name, $this->suffix);
        return $template;
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return string
     */
    public function resolve($name, Renderer $renderer = null)
    {
        // Skip non-theme template
        if (false !== strpos($name, ':')) {
            return false;
        }
        // Get template path from template name
        $path = $this->canonizeTemplate($name);
        // Return the path if valid
        if (file_exists($path)) {
            return $path;
        }
        // Get parent theme
        $parent = Pi::service('theme')->getParent();
        // Try to get template from parent theme
        if ($parent) {
            $path = $this->canonizeTemplate($name, $parent);
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }
}
