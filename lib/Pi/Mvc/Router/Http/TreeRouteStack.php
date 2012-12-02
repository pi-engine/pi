<?php
/**
 * Router
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

namespace Pi\Mvc\Router\Http;

use Zend\Mvc\Router\Http\TreeRouteStack as RouteStack;

use Pi;
use Pi\Mvc\Router\RoutePluginManager;
use Zend\Mvc\Router\PriorityList;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\SimpleRouteStack;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Uri\Http as HttpUri;
use Zend\Mvc\Router\Http\RouteMatch;

class TreeRouteStack extends RouteStack
{
    /**
     * Stack containing all extra routes, potentially for assemble()
     *
     * @var PriorityList
     */
    protected $routesExtra;

    /**
     * Base URL.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Request URI.
     *
     * @var HttpUri
     */
    protected $requestUri;

    /**
     * Create a new simple route stack.
     *
     * @return void
     */
    public function __construct()
    {
        $this->routes               = new PriorityList();
        $this->routePluginManager   = new RoutePluginManager();

        $this->init();
    }

    /**
     * init(): defined by SimpleRouteStack.
     *
     * @see    SimpleRouteStack::init()
     */
    protected function init()
    {
        $this->routePluginManager->setSubNamespace('Http');
    }

    /**
     * routeFromArray(): defined by SimpleRouteStack.
     *
     * @see    SimpleRouteStack::routeFromArray()
     * @param  array|Traversable $specs
     * @return Route
     */
    protected function routeFromArray($specs)
    {
        $route = parent::routeFromArray($specs);

        if (!$route instanceof RouteInterface) {
            throw new \RuntimeException('Given route does not implement HTTP route interface');
        }

        if (isset($specs['child_routes'])) {
            $options = array(
                'route'         => $route,
                'may_terminate' => (isset($specs['may_terminate']) && $specs['may_terminate']),
                'child_routes'  => $specs['child_routes'],
                'route_plugins' => $this->routePluginManager,
            );

            $priority = (isset($route->priority) ? $route->priority : null);

            $route = $this->routePluginManager->get('part', $options);
            $route->priority = $priority;
        }

        return $route;
    }

    /**
     * match(): defined by BaseRoute interface.
     *
     * @see    Route::match()
     * @param  Request $request
     * @return RouteMatch
     */
    public function match(Request $request)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        if ($this->baseUrl === null && method_exists($request, 'getBaseUrl')) {
            $this->setBaseUrl($request->getBaseUrl());
        }

        $uri           = $request->getUri();
        $baseUrlLength = strlen($this->baseUrl) ?: null;

        if ($this->requestUri === null) {
            $this->setRequestUri($uri);
        }

        // Match aginst base URI
        if ($baseUrlLength !== null) {
            $pathLength = strlen($uri->getPath()) - $baseUrlLength;

            foreach ($this->routes as $name => $route) {

                //if (($match = $route->match($request, $baseUrlLength)) instanceof RouteMatch && $match->getLength() === $pathLength) {
                $match = $route->match($request, $baseUrlLength);
                if (!$match instanceof RouteMatch) {
                    continue;
                }
                $matchedLength = $match->getLength();
                if ($matchedLength === $pathLength) {
                    $match->setMatchedRouteName($name);

                    foreach ($this->defaultParams as $name => $value) {
                        if ($match->getParam($name) === null) {
                            $match->setParam($name, $value);
                        }
                    }
                    return $match;
                }
            }
        // Match aginst simple URI
        } else {
            //return parent::match($request);
            foreach ($this->routes as $name => $route) {
                $match = $route->match($request);
                if ($match instanceof RouteMatch) {
                    $match->setMatchedRouteName($name);

                    foreach ($this->defaultParams as $paramName => $value) {
                        if ($match->getParam($paramName) === null) {
                            $match->setParam($paramName, $value);
                        }
                    }

                    return $match;
                }
            }
        }

        return null;
    }

    /**
     * assemble(): defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!isset($options['name'])) {
            throw new \InvalidArgumentException('Missing "name" option');
        }

        $names  = explode('/', $options['name'], 2);
        $name   = $this->canonizeRoute($names[0]);
        $route  = $this->routes->get($name);

        /**#@+
         *  Load extra routes if called route is not found in current route list
         */
        if (!$route) {
            $route = $this->extraRoute($name);
        }
        /**#@-**/

        if (!$route) {
            throw new \RuntimeException(sprintf('Route with name "%s" not found', $name));
        }

        if (isset($names[1])) {
            $options['name'] = $this->canonizeRoute($names[1]);
        } else {
            unset($options['name']);
        }

        if (!isset($options['only_return_path']) || !$options['only_return_path']) {
            if (!isset($options['uri'])) {
                $uri = new HttpUri();

                if (isset($options['force_canonical']) && $options['force_canonical']) {
                    if ($this->requestUri === null) {
                        throw new \RuntimeException('Request URI has not been set');
                    }

                    $uri->setScheme($this->requestUri->getScheme())
                        ->setHost($this->requestUri->getHost())
                        ->setPort($this->requestUri->getPort());
                }

                $options['uri'] = $uri;
            } else {
                $uri = $options['uri'];
            }

            $path = $this->baseUrl . $route->assemble(array_merge($this->defaultParams, $params), $options);

            if ((isset($options['force_canonical']) && $options['force_canonical']) || $uri->getHost() !== null) {
                if ($uri->getScheme() === null) {
                    if ($this->requestUri === null) {
                        throw new \RuntimeException('Request URI has not been set');
                    }

                    $uri->setScheme($this->requestUri->getScheme());
                }

                return $uri->setPath($path)->normalize()->toString();
            } elseif (!$uri->isAbsolute() && $uri->isValidRelative()) {
                return $uri->setPath($path)->normalize()->toString();
            }
        }

        return $this->baseUrl . $route->assemble(array_merge($this->defaultParams, $params), $options);
    }

    /**
     * Set the base URL.
     *
     * @param  string $baseUrl
     * @return self
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * Get the base URL.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set the request URI.
     *
     * @param  HttpUri $uri
     * @return self
     */
    public function setRequestUri(HttpUri $uri)
    {
        $this->requestUri = $uri;
        return $this;
    }

    /**
     * Get the request URI.
     *
     * @return HttpUri
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Get an extra route which does not belong to current section; If the extra routes stack is not loaded, load them from route registry cache
     *
     * @param string $name
     * @return RouteInterface|null
     */
    protected function extraRoute($name)
    {
        if (null == $this->routesExtra) {
            $this->routesExtra = new PriorityList();
            $extraConfig = (array) Pi::service('registry')->route->read(Pi::engine()->section(), true);
            foreach ($extraConfig as $key => $options) {
                $route = $this->routeFromArray($options);
                $priority = isset($route->priority) ? $route->priority : null;
                $this->routesExtra->insert($key, $route, $priority);
            }
        }
        return $this->routesExtra->get($name);
    }

    /**
     * Canonizes relative module route by transliterate [.route] to [module-route]
     *
     * @param string $name
     * @return string
     */
    protected function canonizeRoute($name)
    {
        if ('.' == $name[0]) {
            $name = Pi::service('module')->current() . '-' . substr($name, 1);
        }

        return $name;
    }
}
