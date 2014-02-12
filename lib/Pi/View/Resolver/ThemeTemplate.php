<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\View\Resolver;

use Pi;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 * Theme template resolver
 *
 * Theme template folders/files skeleton:
 *  `theme/default/template/`
 *
 * @see Pi\View\Resolver\ModuleTemplate for module template skeleton
 * @see Pi\View\Resolver\ComponentTemplate for component template skeleton
 * @see Pi\Application\Service\Asset for asset skeleton
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ThemeTemplate implements ResolverInterface
{
    /**
     * Theme template diretory
     * @var string
     */
    protected $templateDirectory = 'template';

    /**
     * Suffix to use: appends this suffix if the template requested
     * does not use it.
     * @var string
     */
    protected $suffix = 'phtml';

    /**
     * Set default file suffix
     *
     * @param  string $suffix
     * @return self
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
        $template = sprintf(
            '%s/%s/%s/%s.%s',
            Pi::path('theme'),
            $theme,
            $this->templateDirectory,
            $name,
            $this->suffix
        );

        return $template;
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return string|false
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
