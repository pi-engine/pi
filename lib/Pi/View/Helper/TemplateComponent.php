<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

/**
 * Helper for system view component template inclusion
 *
 * Usage inside a phtml template
 *
 * ```
 *  include $this->templateComponent('form');
 *  include $this->templateComponent('forms');
 * ```
 *
 * @see    Pi\View\Resolver\ModuleTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TemplateComponent extends TemplateModule
{
    /**
     * Get full path to a system view component template
     *
     * @param string $template
     * @param string $module
     *
     * @return  string
     */
    public function __invoke($template, $module = 'system')
    {
        return parent::__invoke('system:component/' . $template);
    }
}
