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
 * Component template resolver
 *
 * Componenet template folders/files skeleton
 *
 *  - Componet native templates
 *    `lib/Pi/Captcha/Image/template/`
 *
 *  - Component custom templates
 *    `theme/default/lib/Pi/Captcha/Image/template/`
 *
 * @see Pi\View\Resolver\ModuleTemplate for module template skeleton
 * @see Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see Pi\Application\Service\Asset for asset skeleton
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ComponentTemplate implements ResolverInterface
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
     * @return array Pair of component name and template file
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
     * @return string|false
      */
    public function resolve($name, Renderer $renderer = null)
    {
        if (false === strpos($name, ':')) {
            return false;
        }
        list($component, $template) = $this->canonizeTemplate($name);
        // Check custom template in theme
        $path = sprintf(
            '%s/%s/%s/%s/%s.%s',
            Pi::path('theme'),
            Pi::config('theme'),
            $component,
            $this->templateDirectory,
            $template,
            $this->suffix
        );
        if (file_exists($path)) {
            return $path;
        }
        // Check local template in module
        $path = sprintf(
            '%s/%s/%s.%s',
            Pi::path($component),
            $this->templateDirectory,
            $template,
            $this->suffix
        );
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }
}
