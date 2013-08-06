<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        if (class_exists($class)) {
            return $class::render($options, $module);
        }

        return $options;
    }
}
