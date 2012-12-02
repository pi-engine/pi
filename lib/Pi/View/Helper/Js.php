<?php
/**
 * JavaScript file helper
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
 * Helper for loading JavaScript files
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->js('file.js');
 *  $this->js(array('f1.js', 'f2.js'));
 * </code>
 */
class Js extends AbstractHelper
{
    /**
     * Load JavaScrpt file
     *
     * @param   string|array $file
     * @return  Js
     */
    public function __invoke($file = null)
    {
        $files = (array) $file;
        foreach ($files as $file) {
            $this->view->headScript()->appendFile($file);
        }
        return $this;
    }
}
