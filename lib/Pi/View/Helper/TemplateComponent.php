<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for system view component template inclusion
 *
 * Usage inside a phtml template
 *
 * ```
 *  include $this->templateComponent('form');
 *  include $this->templateComponent('form-vertical');
 *  include $this->templateComponent('form-popup');
 * ```
 *
 * @see Pi\View\Resolver\ModuleTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TemplateComponent extends TemplateModule
{
    /**
     * Get full path to a system view component template
     *
     * @param   string  $template
     * @return  string
     */
    public function __invoke($template, $module = 'system')
    {
        return parent::__invoke('system:component/' . $template);
    }
}
