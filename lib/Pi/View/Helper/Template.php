<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for template inclusion
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load a template from a specific module
 *  include $this->template('module/demo:admin/public_index.phtml');
 *  // Or
 *  include $this->template('public_index.phtml', 'admin', 'demo');
 *
 *  // Load a template from current module
 *  include $this->template('admin/public_index.phtml');
 *  // Or
 *  include $this->template('public_index.phtml', 'admin');
 *
 *  // Load a template from current module of current section
 *  include $this->template('./public_index.phtml');
 *  // Or
 *  include $this->template('public_index.phtml', '');
 *
 *  // Load a component template
 *  include $this->template('lib/Pi/Captcha/Image:form.phtml');
 *
 *  // Load a theme template
 *  include $this->template('header.phtml');
 * ```
 *
 * @see Pi\View\Resolver\ModuleTemplate
 * @see Pi\View\Resolver\ThemeTemplate
 * @see Pi\View\Resolver\ComponentTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Template extends AbstractHelper
{
    /**
     * Get full path to a module template
     *
     * @param   string  $template
     * @return  string
     */
    public function __invoke($template)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $template = $args[0];
            $section = $args[1] ?: Pi::engine()->application()->getSection();
            if (!empty($args[2])) {
                $module = $args[2];
            } else {
                $module = Pi::service('module')->current();
            }
            $template = sprintf('module/%s:%s/%s', $module, $section, $template);
        } elseif ('./' == substr($template, 0, 2)) {
            $section = Pi::engine()->application()->getSection();
            $template = $section . substr($template, 1);
        }
        return $this->getView()->resolver($template);
    }
}
