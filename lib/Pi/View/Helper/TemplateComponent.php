<?php
/**
 * Component template inclusion helper
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
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
//use Zend\View\Helper\AbstractHelper;

/**
 * Helper for view component template in system module
 * @see Pi\View\Resolver\ModuleTemplate
 *
 * Usage inside a phtml template:
 * <code>
 *  include $this->templateComponent('form');
 *  include $this->templateComponent('form-vertical');
 *  include $this->templateComponent('form-popup');
 * </code>
 */
class TemplateComponent extends TemplateModule
{
    /**
     * Get full path to a module template
     *
     * @param   string  $template
     * @return  string
     */
    public function __invoke($template, $module = 'system')
    {
        return parent::__invoke('system:component/' . $template);
    }
}
