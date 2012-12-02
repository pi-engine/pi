<?php
/**
 * Route Broker
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
 * @package         Pi\Mvc
 * @subpackage      Router
 * @version         $Id$
 */

namespace Pi\Mvc\Router;

use Zend\Mvc\Router\RouteBroker as ZendBroker;

class RouteBroker extends ZendBroker
{
    /**
     * load(): defined by Broker interface.
     *
     * @see    Broker::load()
     * @param  string $route
     * @param  array  $options
     * @return Route
     */
    public function load($route, array $options = array())
    {
        if (class_exists($route)) {
            // Allow loading fully-qualified class names via the broker
            $class = $route;
        } else {
            // Load local route
            $class = __NAMESPACE__ . '\\Route\\' . ucfirst($route);

            if (empty($class)) {
                throw new \RuntimeException('Unable to locate class associated with "' . $route . '"');
            }
        }

        return $class::factory($options);
    }
}
