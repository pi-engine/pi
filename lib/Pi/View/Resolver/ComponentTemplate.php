<?php
/**
 * Component template resolver
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
 * Component template resolver
 * @see \Pi\View\Resolver\ModuleTemplate for theme template skeleton
 * @see \Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see \Pi\Application\Service\Asset for asset skeleton
 *
 * Componenet template folders/files skeleton
 * <ul>
 *          <li>Componet native templates:
 *              <code>lib/Pi/Captcha/Image/template/</code>
 *          </li>
 *          <li>Component custom templates:
 *              <code>theme/default/lib/Pi/Captcha/Image/template/</code>
 *          </li>
 * </ul>
 */

/**
 * Resolves component view scripts
 *
 * @see Zend\View\Resolver\ResolverInterface
 */
class ComponentTemplate implements ResolverInterface
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
     * @return array
     */
    protected function canonizeTemplate($name)
    {
        if (substr($name, -6) == '.' . $this->suffix) {
            $name = substr($name, 0, -6);
        }
        list($component, $template) = explode(':', $name, 2);
        return array($component, $template);
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
        if (false === strpos($name, ':')) {
            return false;
        }
        list($component, $template) = $this->canonizeTemplate($name);
        // Check custom template in theme
        $path = sprintf('%s/%s/%s/%s/%s.%s', Pi::path('theme'), Pi::config('theme'), $component, $this->templateDirectory, $template, $this->suffix);
        if (file_exists($path)) {
            return $path;
        }
        // Check local template in module
        $path = sprintf('%s/%s/%s.%s', Pi::path($component), $this->templateDirectory, $template, $this->suffix);
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }
}
