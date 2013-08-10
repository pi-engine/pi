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
 * Module template resolver
 *
 * Usage
 *
 *  ```
 *      // Full path
 *      $model->setTemplate('/full/path/to/template.html');
 *      // Relative path with specified module
 *      $model->setTemplate('module:path/to/template');
 *      // Relative path w/o specified module
 *      $model->setTemplate('path/to/template');
 *  ```
 *
 * Look up in module template folders
 *
 * - Module custom templates in a theme:
 *   - for module "demo":
 *      `theme/default/module/demo/template/[front/template.html]`
 *   - for module "democlone":
 *      'theme/default/module/democlone/template/[front/template.html]`
 *
 * - Module native templates:
 *   - for both module "demo" and cloned "democlone":
 *      `module/demo/template/[front/template.html]`
 *
 * @see Pi\View\Resolver\ThemeTemplate for theme template skeleton
 * @see Pi\View\Resolver\ComponentTemplate for component template skeleton
 * @see Pi\Application\Service\Asset for asset skeleton
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleTemplate implements ResolverInterface
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
     * @return array|string Pair of module and template name,
     *      or full path to template
     */
    protected function canonizeTemplate($name)
    {
        // Empty template
        if ('__NULL__' == $name) {
            return array('system', 'dummy');
        }
        // With suffix
        if (substr($name, -6) == '.' . $this->suffix) {
            // Full path to template
            if (file_exists($name)) {
                return $name;
            }
            // Remove suffix
            $name = substr($name, 0, -6);
        }
        $segs = explode(':', $name, 2);
        if (isset($segs[1])) {
            list($module, $template) = $segs;
        } else {
            $module = Pi::service('module')->current();
            $template = $name;
        }

        return array($module, $template);
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @param  string $name Relative or full path to template,
     *      it is highly recommended to remove suffix from relative template
     * @param  null|Renderer $renderer
     * @return string|false
      */
    public function resolve($name, Renderer $renderer = null)
    {
        $return = $this->canonizeTemplate($name);
        if (!is_array($return)) {
            return $return;
        }
        list($module, $template) = $return;
        // Check custom template in theme
        $path = sprintf(
            '%s/%s/module/%s/%s/%s.%s',
            Pi::path('theme'),
            Pi::service('theme')->current(),
            $module,
            $this->templateDirectory,
            $template,
            $this->suffix
        );
        if (file_exists($path)) {
            return $path;
        }
        // Check local template in module
        $path = sprintf(
            '%s/%s/%s/%s.%s',
            Pi::path('module'),
            Pi::service('module')->directory($module),
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
