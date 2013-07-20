<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

class Router extends AbstractResource
{
    /**
     * Retrieve router object
     *
     * @return \Zend\Mvc\Router\RouteStackInterface
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
