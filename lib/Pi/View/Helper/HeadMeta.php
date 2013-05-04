<?php
/**
 * HeadMeta
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
use Zend\View\Helper\HeadMeta as ZendHeadMeta;
use Zend\View\Helper\Placeholder;

/**
 * Helper for setting and building meta elements for HTML head section
 *
 * TODO: To reset global meta for keywords/description
 *
 * @see ZendHeadMeta for details.
 */
class HeadMeta extends ZendHeadMeta
{
    /**
     * Retrieve object instance; optionally add meta tag
     *
     * @param  string $content
     * @param  string $keyValue
     * @param  string $keyType
     * @param  array $modifiers
     * @param  string $placement
     * @return HeadMeta
     */
    public function __invoke($content = null, $keyValue = null, $keyType = 'name', $modifiers = array(), $placement = null)
    {
        if (null === $placement) {
            $placement = Placeholder\Container\AbstractContainer::SET;
        }
        parent::__invoke($content, $keyValue, $keyType, $modifiers, $placement);
        return $this;
    }

    /**
     * Append
     *
     * @param  string $value
     * @return void
     */
    public function append($value)
    {
        if ('name' == $value->type) {
            $container = $this->getContainer();
            $content = '';
            foreach ($container->getArrayCopy() as $index => $item) {
                if ($item->name == $value->name) {
                    $content = $item->content;
                    $this->offsetUnset($index);
                }
            }
            if ($content) {
                $separator = ('description' == $value->name) ? ' ' : ', ';
                $value->content = $content . $separator . $value->content;
            }
        }

        return parent::append($value);
    }

    /**
     * Prepend
     *
     * @param  string $value
     * @return void
     */
    public function prepend($value)
    {
        if ('name' == $value->type) {
            $container = $this->getContainer();
            $content = '';
            foreach ($container->getArrayCopy() as $index => $item) {
                if ($item->name == $value->name) {
                    $content = $item->content;
                    $this->offsetUnset($index);
                }
            }
            if ($content) {
                $separator = ('description' == $value->name) ? ' ' : ', ';
                $value->content = $value->content . $separator . $content;
            }
        }

        return parent::prepend($value);
    }
}