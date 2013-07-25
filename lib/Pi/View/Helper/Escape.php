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
use Zend\View\Helper\Escaper;

/**
 * Helper for escape HTML content, alias to escapeHtml
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->escape($content);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
