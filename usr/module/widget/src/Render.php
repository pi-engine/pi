<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget;

class Render
{
    /**
     * Magic method to access custom widget renderer
     *
     * @param string $name  Custom widget name
     * @param array  $args  Block options
     *
     * @return mixed
     */
    public static function __callStatic($name, $args = null)
    {
        $options = array_shift($args);
        $module = array_shift($args);

        $className = str_replace(' ', '', ucwords(str_replace(array('_', '-', '.'), ' ', strtolower($name))));
        $class = 'Custom\Widget\Render\\' . $className;
        if (!class_exists($class)) {
            $class = 'Module\Widget\Render\\' . $className;
            if (!class_exists($class)) {
                $class = '';
            }
        }
        if ($class) {
            return $class::render($options, $module);
        }

        return $options;
    }
}
