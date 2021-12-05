<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\View\Modal;

use Laminas\View\Model\ViewModel as LaminasViewModel;

/**
 * @see    Laminas\View\Model\ViewModel
 * @author Javad Karimi <karimijavad990@gmail.com>
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class ViewModel extends LaminasViewModel
{
    /**
     * Get a single view variable
     *
     * @param  string       $name
     * @param  mixed|null   $default (optional) default value if the variable is not present.
     * @return mixed
     */
    public function getVariable($name, $default = null)
    {
        $name = (string)$name;
        if (is_array($this->variables)) {
            if (array_key_exists($name, $this->variables)) {
                return $this->variables[$name];
            }
        }

        return $default;
    }
}