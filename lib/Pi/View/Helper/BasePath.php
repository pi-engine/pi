<?php
/**
 * Base Path helper
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
 * Helper for base path
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->basePath();
 *  $this->basepath($file);
 * </code>
 */
class BasePath extends AbstractHelper
{
    /**
     * Get base path
     *
     * @param   string $file
     * @return  string
     */
    public function __invoke($file = null)
    {
        return Pi::url('www') . ((null === $file) ? '' : '/' . $file);
    }
}
