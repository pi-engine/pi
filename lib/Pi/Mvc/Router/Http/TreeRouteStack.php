<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router\Http;

use Pi;
use Pi\Mvc\Router\PriorityList;
use Pi\Mvc\Router\RoutePluginManager;
use Zend\Mvc\Router\Http\TreeRouteStack as ZendTreeRouteStack;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\Http\RouteMatch;

/**
 * Tree RouteStack
 *
 * Note:
 * Route names for cloned modules are indexed by a string composed of module
 * name and route name
 *
 * @see     Pi\Application\Registry\Route
 * @author  Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TreeRouteStack extends ZendTreeRouteStack
{
    /**
     * Create a new simple route stack.
     *
     * @param RoutePluginManager $routePluginManager
     */
    public function __construct(RoutePluginManager $routePluginManager = null)
    {
        $this->routes = new PriorityList();

        if (null === $routePluginManager) {
            $routePluginManager = new RoutePluginManager();
        }

        $this->routePluginManager = $routePluginManager;

        $this->init();
    }

    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        $this->routes->setExtraRouteLoader(array($this, 'loadExtraRoutes'));
        $this->routePluginManager->setSubNamespace('Http');
    }

    /**
     * {@inheritDoc}
     * Canonize matched route name for cloned modules
     */
    public function match(
        Request $request,
        $pathOffset = null,
        array $options = array()
    ) {
        $routeMatch = parent::match($request, $pathOffset, $options);
        if ($routeMatch) {
            $module = $routeMatch->getParam('module');
            $directory = Pi::service('module')->directory($module);
            if ($directory && $module != $directory) {
                $name = $routeMatch->getMatchedRouteName();
                // <module>-<name> => <name>
                $routeList = Pi::registry('RouteList')->read($directory);
                if ($routeList && isset($routeList[$name])) {
                    // Remove prepended module name to route name for cloned modules
                    $name = substr($name, strlen($module) + 1);
                    $routeMatch->setMatchedRouteName($name);
                }
            }
        }

        return $routeMatch;
    }

    /**
     * {@inheritDoc}
     * Canonize route name for cloned modules
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!empty($options['name']) && !empty($params['module'])) {
            $module = $params['module'];
            $directory = Pi::service('module')->directory($module);
            if ($module != $directory) {
                $routeList = Pi::registry('route_list')->read($directory);
                $names = explode('/', $options['name'], 2);
                if ($routeList && isset($routeList[$names[0]])) {
                    // Prepend module name to route name for cloned modules
                    $options['name'] = $module . '-' . $options['name'];
                }
               // d($params);
            }
        }

        return parent::assemble($params, $options);
    }

    /**
     * Parse by specified route
     *
     * @param Request   $request
     * @param string    $name
     * @param array     $options
     *
     * @return RouteMatch|null
     */
    public function parse(Request $request, $name, array $options = array())
    {
        $uri            = $request->getUri();
        $baseUrlLength  = strlen($this->baseUrl) ?: null;
        $route          = $this->routes->get($name);

        if ($baseUrlLength !== null) {
            $pathLength = strlen($uri->getPath()) - $baseUrlLength;
        } else {
            $pathLength = null;
        }
        if (
            ($match = $route->match($request, $baseUrlLength, $options)) instanceof RouteMatch
            && ($pathLength === null || $match->getLength() === $pathLength)
        ) {
            $match->setMatchedRouteName($name);

            foreach ($this->defaultParams as $paramName => $value) {
                if ($match->getParam($paramName) === null) {
                    $match->setParam($paramName, $value);
                }
            }
        } else {
            $match = null;
        }

        return $match;
    }

    /**
     * Get an extra route which does not belong to current section;
     * If the extra routes stack is not loaded,
     * load them from route registry cache
     *
     * @return array
     */
    public function loadExtraRoutes()
    {
        $extraRoutes = array();
        $extraConfig = (array) Pi::registry('route')->read(
            Pi::engine()->section(),
            true
        );
        foreach ($extraConfig as $key => $options) {
            $route = $this->routeFromArray($options);
            $extraRoutes[$key] = $route;
        }

        return $extraRoutes;
    }
}
