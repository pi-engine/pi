<?php
/**
 * Laminas Framework (http://framework.Laminas.com/)
 *
 * @link      http://github.com/Laminasframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Laminas Technologies USA Inc. (http://www.Laminas.com)
 * @license   http://framework.Laminas.com/license/new-bsd New BSD License
 */

namespace Pi\Command\Mvc;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Console\RouteMatch;

/**
 * Route listener for command line
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class RouteListener extends AbstractListenerAggregate
{
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute']);
    }

    /**
     * Listen to the "route" event and attempt to route the request
     *
     * If no matches are returned, triggers "dispatch.error" in order to
     * create a 404 response.
     *
     * Seeds the event with the route match on completion.
     *
     * @param  MvcEvent $e
     * @return null|Router\RouteMatch
     */
    public function onRoute($e)
    {
        $target     = $e->getTarget();
        $request    = $e->getRequest();
        $router     = $e->getRouter();
        $routeMatch = $router->match($request);

        if (!$routeMatch instanceof RouteMatch) {
            $e->setError(Application::ERROR_ROUTER_NO_MATCH);

            $results = $target->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $e);
            if (count($results)) {
                $return = $results->last();
            } else {
                $return = $e->getParams();
            }
            return $return;
        }

        $e->setRouteMatch($routeMatch);
        return $routeMatch;
    }
}
