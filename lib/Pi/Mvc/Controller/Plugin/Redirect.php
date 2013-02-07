<?php
/**
 * Controller plugin Redirect class
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
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\Redirect as ZendRedirect;
use Zend\Http\Response;

class Redirect extends ZendRedirect
{
    protected $responseCode;

    /**
     * Generates a URL based on a route
     *
     * @param  string $route RouteInterface name
     * @param  array $params Parameters to use in url generation, if any
     * @param  array $options RouteInterface-specific options to use in url generation, if any
     * @return Response|Redirect
     */
    public function __invoke($route = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if (!$route && !$params) {
            return $this;
        }
        return $this->toRoute($route, $params, $options, $reuseMatchedParams);
    }

    /**
     * Set response status code
     *
     * @param int $code
     * @return Redirect
     */
    public function setStatusCode($code)
    {
        $this->responseCode = $code;
        return $this;
    }

    /**
     * Generates a URL based on a route
     *
     * @param  string $route RouteInterface name
     * @param  array $params Parameters to use in url generation, if any
     * @param  array $options RouteInterface-specific options to use in url generation, if any
     * @return Response
     */
    public function toRoute($route = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $routeMatch = null;
        if (!$route) {
            $routeMatch = $this->getEvent()->getRouteMatch();
            $route = $routeMatch->getMatchedRouteName();
        }
        if (!isset($params['module'])) {
            $routeMatch = $routeMatch ?: $this->getEvent()->getRouteMatch();
            $params['module'] = $routeMatch->getParam('module');
            if (!isset($params['controller'])) {
                $params['controller'] = $routeMatch->getParam('controller');
            }
        }
        $this->controller->view()->setTemplate(false);

        $response = parent::toRoute($route, $params, $options, $reuseMatchedParams);
        if ($this->responseCode) {
            $response->setStatusCode($this->responseCode);
            $this->responseCode = null;
        }
        $response->send();
        return $response;
        //exit();
    }

    /**
     * Redirect to the given URL
     *
     * @param  string $url
     * @return Response
     */
    public function toUrl($url)
    {
        $this->controller->view()->setTemplate(false);
        $response = parent::toUrl($url);
        if ($this->responseCode) {
            $response->setStatusCode($this->responseCode);
            $this->responseCode = null;
        }
        $response->send();
        return $response;
    }
}
