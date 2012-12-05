<?php
/**
 * FootScript
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

/**
 * Helper for setting and retrieving script elements for HTML foot section
 *
 * @see HeadScript for details.
 * A new use case with raw type content:
 * <code>
 *  if (false !== stripos($script, '<script ')) {
 *      $view->footScript()->appendScript($script, 'raw');
 *  } else {
 *      $view->footScript()->appendScript($script);
 *  }
 * </code>
 */
class FootScript extends HeadScript
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $regKey = 'Pi_View_Helper_FootScript';

    /**
     * Create script HTML
     *
     * @param  mixed  $item        Item to convert
     * @param  string $indent      String to add before the item
     * @param  string $escapeStart Starting sequence
     * @param  string $escapeEnd   Ending sequence
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        if ('raw' == $item->type) {
            return $item->source;
        }
        return parent::itemToString($item, $indent, $escapeStart, $escapeEnd);
    }
}
