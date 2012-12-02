<?php
/**
 * Theme template inclusion helper
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
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for theme template inclusion
 * @see Pi\View\Resolver\ThemeTemplate
 *
 * Usage inside a phtml template:
 * <code>
 *  include $this->templateTheme('header.phtml');
 * </code>
 */
class TemplateTheme extends AbstractHelper
{
    /**
     * Get full path to a theme template
     *
     * @param   string  $template
     * @param   string|null $theme, not implemented yet
     * @return  string
     */
    public function __invoke($template, $theme = null)
    {
        return $this->getView()->resolver($template);
    }
}
