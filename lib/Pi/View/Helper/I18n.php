<?php
/**
 * i18n helper
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
 * Helper for loading Intl resource
 * @see Pi\Application\Service\I18n
 * @see Pi\Application\Service\Asset
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->i18n('theme/default', 'main');
 *  $this->i18n('module/demo', 'block');
 * </code>
 */
class I18n extends AbstractHelper
{
    /**
     * Load an i18n resource
     *
     * @param   string  $component
     * @param   string  $file
     * @return  string
     */
    public function __invoke($component, $file)
    {
        return Pi::service('i18n')->load($component, $file);
    }
}
