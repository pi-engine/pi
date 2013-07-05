<?php
/**
 * Widget renderer proxy
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
 * @since           1.0
 * @package         Module\Widget
 * @version         $Id$
 */

namespace Module\Widget;

use Pi;

class Render
{
    /**
     * Magic method to access custom widget renderer
     *
     * @param string $name Custom widget name
     * @param array  block options
     */
    public static function __callStatic($name, $args = null)
    {
        $options = array_shift($args);
        //return $options;

        $module = array_shift($args);
        $class = __NAMESPACE__ . '\\Render\\' . ucfirst($name);
        /*
        if (!class_exists($class)) {
            include Pi::service('module')->path($module) . '/usr/' . $name . '/Renderer.php';
        }
        */
        if (class_exists($class)) {
            return $class::render($options, $module);
        }
        
        return $options;
    }
    /*#@-*/
}
