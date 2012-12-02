<?php
/**
 * Bootstrap resource
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
 * @package         Pi\Application
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Resource;

use Pi;

class Router extends AbstractResource
{
    /**
     * Retrieve router object
     *
     * @return
     */
    public function boot()
    {
        $options = $this->options;
        $routerClass = !empty($options['class']) ? $options['class'] : 'Pi\\Mvc\\Router\\Http\\TreeRouteStack';

        $section = !empty($options['section']) ? $options['section'] : Pi::engine()->section();
        $routes = Pi::service('registry')->route->read($section, $exclude = 0);
        if (!empty($options['routes'])) {
            $routes = array_merge($routes, $options['routes']);
        }
        $options['routes'] =  $routes;
        $router = $routerClass::factory($options);

        if (is_callable(array($router, 'setBaseUrl'))) {
            $router->setBaseUrl(Pi::host()->get('baseUrl'));
        }

        $this->application->getServiceManager()->setService('router', $router);

        return $router;
    }
}
