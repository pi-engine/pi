<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * Route loading
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Router extends AbstractResource
{
    /**
     * Retrieve router object
     *
     * @return \Laminas\Mvc\Router\RouteStackInterface
     */
    public function boot()
    {
        $options     = $this->options;
        $routerClass = !empty($options['class']) ? $options['class'] : 'Pi\Mvc\Router\Http\TreeRouteStack';

        $router = $routerClass::factory();
        $router->load($options);

        if (is_callable([$router, 'setBaseUrl'])) {
            $router->setBaseUrl(Pi::host()->get('baseUrl'));
        }

        $this->application->getServiceManager()->setService('router', $router);

        return $router;
    }
}
