<?php
/**
 * HTML escape helper
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
use Zend\View\Helper\Escaper;

/**
 * Helper for escape HTML content, alias to escapeHtml
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->escape($content);
 * </code>
 */
class Escape extends Escaper\AbstractHelper
{
    /**
     * Escape a value for current escaping strategy
     *
     * @param string $value
     * @return string
     */
    protected function escape($value)
    {
        return $this->getEscaper()->escapeHtml($value);
    }
}
