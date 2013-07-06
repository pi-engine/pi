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
 * @package         Pi\View
 * @subpackage      Helper
 */

namespace Pi\View\Helper;

use Pi;
use stdClass;
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
     * {@inheritDoc}
     */
    public function __invoke(array $attributes = null, $placement = Placeholder\Container\AbstractContainer::APPEND)
    {
        parent::__invoke($attributes, strtoupper($placement));
        return $this;
    }

    /**
     *  Canonize attribute 'conditional' with 'conditionalStylesheet'
     * {@inheritDoc}
     */
    public function itemToString(stdClass $item)
    {
        if (isset($item->conditional)) {
            $item->conditionalStylesheet = $item->conditional;
            $item->conditional = null;
        }

        return parent::itemToString($item);
    }

}