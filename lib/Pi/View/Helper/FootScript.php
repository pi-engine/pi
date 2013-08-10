<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for setting and retrieving script elements for HTML foot section
 *
 * A new use case with raw type content
 *
 * ```
 *  if (false !== stripos($script, '<script ')) {
 *      $view->footScript()->appendScript($script, 'raw');
 *  } else {
 *      $view->footScript()->appendScript($script);
 *  }
 * ```
 *
 * @see Pi\View\Helper\HeadScript for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
