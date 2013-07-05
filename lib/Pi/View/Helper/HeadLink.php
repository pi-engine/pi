<?php
/**
 * HeadLink
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
use Zend\View\Helper\HeadLink as ZendHeadLink;
use Zend\View\Helper\Placeholder;

/**
 * Helper for setting and retrieving link element for HTML head
 *
 * @see ZendHeadLink for details.
 */
class HeadLink extends ZendHeadLink
{
    /**
     * headLink() - View Helper Method
     *
     * Returns current object instance. Optionally, allows passing array of
     * values to build link.
     *
     * @param array $attributes
     * @param string $placement
     * @return HeadLink
     */
    public function __invoke(array $attributes = null, $placement = Placeholder\Container\AbstractContainer::APPEND)
    {
        parent::__invoke($attributes, strtoupper($placement));
        return $this;
    }
}