<?php
/**
 * Module template inclusion helper
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
use Pi\View\Resolver\ModuleTemplate as ModuleTemplateResolver;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for module template inclusion
 * @see Pi\View\Resolver\ModuleTemplate
 *
 * Usage inside a phtml template:
 * <code>
 *  include $this->templateModule('admin/public-index.phtml');
 *  include $this->templateModule('admin/public-index.phtml', 'demo');
 * </code>
 */
class TemplateModule extends AbstractHelper
{
    /**
     * Get full path to a module template
     *
     * @param   string  $template
     * @param   string|null $module
     * @return  string|false
     */
    public function __invoke($template, $module = null)
    {
        $template = $module ? $module . ':' . $template : $template;
        //$template = $this->getView()->resolver($template);

        $resolver = new ModuleTemplateResolver;
        $template = $resolver->resolve($template);

        return $template;
    }
}
