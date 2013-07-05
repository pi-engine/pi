<?php
/**
 * HeadTitle
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
use Zend\View\Helper\HeadTitle as ZendHeadTitle;
use Zend\View\Helper\Placeholder;

/**
 * Helper for setting and retrieving title element for HTML head
 *
 * @see ZendHeadTitle for details.
 */
class HeadTitle extends ZendHeadTitle
{
    /**
     * Retrieve placeholder for title element and optionally set state
     *
     * @param  string $title
     * @param  string $setType
     * @return HeadTitle
     */
    public function __invoke($title = null, $setType = null)
    {
        if (null !== $setType) {
            $setType = strtoupper($setType);
        }
        return parent::__invoke($title, $setType);
    }

    /**
     * Display content with specified indentation string
     *
     * @param  null|string|int $indent
     * @return void
     */
    public function ____render($indent = null)
    {
        if (null !== $indent) {
            $this->setIndent($indent);
        }
        echo $this->toString();
    }
}