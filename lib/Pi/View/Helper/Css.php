<?php
/**
 * CSS file helper
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
 * Helper for loading CSS files
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->css('file.css');
 *  $this->js(array('f1.css', 'f2.css'));
 * </code>
 */
class Css extends AbstractHelper
{
    /**
     * Load CSS file
     *
     * @param   string|array $file
     * @param   string $position append or prepend, default as 'append'
     * @return  Css
     */
    public function __invoke($file = null, $position = 'append')
    {
        $files = (array) $file;
        $helper = $this->view->headLink();
        foreach ($files as $file) {
            if ('prepend' == $position) {
                $helper->prependStylesheet($file);
            } else {
                $helper->appendStylesheet($file);
            }
        }
        return $this;
    }
}
