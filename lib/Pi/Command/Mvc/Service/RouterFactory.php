<?php
/**
 * Laminas Framework (http://framework.Laminas.com/)
 *
 * @link      http://github.com/Laminasframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Laminas Technologies USA Inc. (http://www.Laminas.com)
 * @license   http://framework.Laminas.com/license/new-bsd New BSD License
 */

namespace Pi\Command\Mvc\Service;

use Laminas\Console\Console;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class RouterFactory implements FactoryInterface
{
    /**
     * Create and return the router
     *
     * Retrieves the "router" key of the Config service, and uses it
     * to instantiate the router. Uses the TreeRouteStack implementation by
     * default.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string|null $cName
     * @param  string|null $rName
     * @return \Laminas\Mvc\Router\RouteStackInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $cName = null, $rName = null)
    {
        $config = $serviceLocator->has('Config') ? $serviceLocator->get('Config') : [];

        // Defaults
        $routerClass  = 'Laminas\Mvc\Router\Http\TreeRouteStack';
        $routerConfig = isset($config['router']) ? $config['router'] : [];

        // Console environment?
        if ($rName === 'ConsoleRouter'                       // force console router
            || ($cName === 'router' && Console::isConsole()) // auto detect console
        ) {
            // We are in a console, use console router defaults.
            $routerClass  = 'Laminas\Mvc\Router\Console\SimpleRouteStack';
            $routerConfig = isset($config['console']['router']) ? $config['console']['router'] : [];
        }

        // Obtain the configured router class, if any
        if (isset($routerConfig['router_class']) && class_exists($routerConfig['router_class'])) {
            $routerClass = $routerConfig['router_class'];
        }

        // Inject the route plugins
        if (!isset($routerConfig['route_plugins'])) {
            $routePluginManager            = $serviceLocator->get('RoutePluginManager');
            $routerConfig['route_plugins'] = $routePluginManager;
        }

        // Obtain an instance
        $factory = sprintf('%s::factory', $routerClass);
        return call_user_func($factory, $routerConfig);
    }
}
