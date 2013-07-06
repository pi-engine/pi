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
 * @package         Pi\View
 * @subpackage      Helper
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for loading CSS files
 *
 * Usage inside a phtml template:
 * <code>
 *  // Load specific file
 *  $this->css('some.css');
 *
 *  // Load specific file with position
 *  $this->css('some.css', 'prepend');
 *
 *  // Load specific file with attributes
 *  $this->css('some.css', array('conditional' => '...', 'postion' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->css(array(
 *      'a.css',
 *      'b.css',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->css(array(
 *      'a.css' => array('media' => '...', 'conditional' => '...'),
 *      'b.css' => array(),
 *  ));
 * </code>
 */
class Css extends AssetCanonize
{
    /**
     * Load CSS file
     *
     * @param   string|array $files
     * @param   string|array $attributes    Only applicable when $files is scalar, default as string for position, append or prepend, default as 'append'
     * @return  Css
     */
    public function __invoke($files = null, $attributes = 'append')
    {
        $files = $this->canonize($files, $attributes);
        $helper = $this->view->headLink();
        foreach ($files as $file => $attrs) {
            $position = isset($file['position']) ? $file['position'] : 'append';
            if ('prepend' == $position) {
                $helper->prependStylesheet($attrs);
            } else {
                $helper->appendStylesheet($attrs);
            }
        }
        return $this;
    }
}
