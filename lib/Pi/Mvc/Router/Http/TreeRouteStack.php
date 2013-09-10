<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Router\Http;

use Pi;
use Pi\Mvc\Router\RoutePluginManager;
use Zend\Mvc\Router\Http\TreeRouteStack as RouteStack;
use Zend\Mvc\Router\PriorityList;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\SimpleRouteStack;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Uri\Http as HttpUri;
use Zend\Mvc\Router\Http\RouteMatch;

/**
 * Tree RouteStack
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TreeRouteStack extends RouteStack
{
    /**
     * Stack containing all extra routes, potentially for assemble()
     * @var PriorityList
     */
    protected $routesExtra;

    /**
     * Base URL.
     * @var string
     */
    protected $baseUrl;

    /**
     * Request URI.
     * @var HttpUri
     */
    protected $requestUri;

    /**
     * Create a new simple route stack.
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
     * @return void
     */
    protected function init()
    {
        $this->routePluginManager->setSubNamespace('Http');
    }

    /**
     * routeFromArray(): defined by SimpleRouteStack.
     *
     * @param array|\Traversable $specs
     *
     * @throws \RuntimeException
     * @return RouteInterface
     */
    protected function routeFromArray($specs)
    {
        $route = parent::routeFromArray($specs);

        if (!$route instanceof RouteInterface) {
            throw new \RuntimeException(
                'Given route does not implement HTTP route interface'
            );
        }

        if (isset($specs['child_routes'])) {
            $options = array(
                'route'         => $route,
                'may_terminate' => (isset($specs['may_terminate'])
                    && $specs['may_terminate']
                ),
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
     * @see Route::match()
     *
     * @param Request   $request
     * @param string    $routeName
     *
     * @return RouteMatch|null
     */
    public function match(Request $request, $routeName = '')
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

        // Specified route
        if ($routeName) {
            $route  = $this->routes->get($routeName)
                ?: $this->extraRoute($routeName);
            $routes = array($routeName => $route);
        // Not specified
        } else {
            $routes = $this->routes;
        }

        // Match against base URI
        if ($baseUrlLength !== null) {
            $pathLength = strlen($uri->getPath()) - $baseUrlLength;
            //foreach ($this->routes as $name => $route) {
            foreach ($routes as $name => $route) {
                $match = $route->match($request, $baseUrlLength);
                if (!$match instanceof RouteMatch) {
                    continue;
                }
                $matchedLength = $match->getLength();
                if ($matchedLength === $pathLength) {
                    $match->setMatchedRouteName($name);

                    foreach ($this->defaultParams as $key => $value) {
                        if ($match->getParam($key) === null) {
                            $match->setParam($key, $value);
                        }
                    }
                    return $match;
                }
            }
        // Match against simple URI
        } else {
            //return parent::match($request);
            //foreach ($this->routes as $name => $route) {
            foreach ($routes as $name => $route) {
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
     *
     * @param  array $params
     * @param  array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return string
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!isset($options['name'])) {
            throw new \InvalidArgumentException('Missing "name" option');
        }

        $names  = explode('/', $options['name'], 2);
        $name   = $names[0];
        $route  = $this->routes->get($name);

        /**#@+
         *  Load extra routes if called route not found in current route list
         */
        if (!$route) {
            $route = $this->extraRoute($name);
        }
        /**#@-**/

        if (!$route) {
            throw new \RuntimeException(
                sprintf('Route with name "%s" not found', $name)
            );
        }

        if (isset($names[1])) {
            $options['name'] = $names[1];
        } else {
            unset($options['name']);
        }

        if (!isset($options['only_return_path'])
            || !$options['only_return_path']
        ) {
            if (!isset($options['uri'])) {
                $uri = new HttpUri();

                if (isset($options['force_canonical'])
                    && $options['force_canonical']
                ) {
                    if ($this->requestUri === null) {
                        throw new \RuntimeException(
                            'Request URI has not been set'
                        );
                    }

                    $uri->setScheme($this->requestUri->getScheme())
                        ->setHost($this->requestUri->getHost())
                        ->setPort($this->requestUri->getPort());
                }

                $options['uri'] = $uri;
            } else {
                $uri = $options['uri'];
            }

            $path = $this->baseUrl
                  . $route->assemble(
                      array_merge($this->defaultParams, $params),
                      $options
                    );

            if ((isset($options['force_canonical'])
                    && $options['force_canonical']
                )
                || $uri->getHost() !== null
            ) {
                if ($uri->getScheme() === null) {
                    if ($this->requestUri === null) {
                        throw new \RuntimeException(
                            'Request URI has not been set'
                        );
                    }

                    $uri->setScheme($this->requestUri->getScheme());
                }

                return $uri->setPath($path)->normalize()->toString();
            } elseif (!$uri->isAbsolute() && $uri->isValidRelative()) {
                return $uri->setPath($path)->normalize()->toString();
            }
        }

        return $this->baseUrl
            . $route->assemble(
                array_merge($this->defaultParams, $params),
                $options
            );
    }

    /**
     * Set the base URL.
     *
     * @param  string $baseUrl
     * @return $this
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
     * @return $this
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
     * Get an extra route which does not belong to current section;
     * If the extra routes stack is not loaded,
     * load them from route registry cache
     *
     * @param string $name
     * @return RouteInterface|null
     */
    protected function extraRoute($name)
    {
        if (null == $this->routesExtra) {
            $this->routesExtra = new PriorityList();
            $extraConfig = (array) Pi::registry('route')->read(
                Pi::engine()->section(),
                true
            );
            foreach ($extraConfig as $key => $options) {
                $route = $this->routeFromArray($options);
                $priority = isset($route->priority) ? $route->priority : null;
                $this->routesExtra->insert($key, $route, $priority);
            }
        }

        return $this->routesExtra->get($name);
    }
}
