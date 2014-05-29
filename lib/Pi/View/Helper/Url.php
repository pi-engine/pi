<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\Mvc\Router\RouteStackInterface;
use Pi\Mvc\Router\RouteMatch;
use Zend\View\Helper\Url as ZendUrl;

/**
 * Helper for assembling URL with routes and parameters
 *
 * - Usage inside a phtml template
 *
 * ```
 *  $this->url('home');
 *  $this->url('default', array('module' => 'demo', 'controller' => 'test');
 * ```
 *
 * - Fetch current request URI
 *
 * ```
 *  $this->url()->requestUri();
 * ```
 *
 * - Fetch current routeMatch
 *
 * ```
 *  $this->url()->routeMatch();
 * ```
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Url extends ZendUrl
{
    /** @var RouteStackInterface Router for URL assemble */
    protected $router;

    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch.
     */
    protected $routeMatch;

    /**
     * Assemble URL
     *
     * {@inheritdoc}
     */
    public function __invoke(
        $name = null,
        $params = array(),
        $options = array(),
        $reuseMatchedParams = false
    ) {
        if (!func_num_args()) {
            return $this;
        }

        return Pi::service('url')->assemble(
            $name,
            $params,
            $options,
            $reuseMatchedParams
        );
    }

    /**
     * Get the router to use for assembling.
     *
     * @return RouteStackInterface $router
     */
    public function router()
    {
        if (!$this->router) {
            $this->router = Pi::engine()->application()->getRouter();
        }

        return $this->router;
    }

    /**
     * Get route match returned by the router.
     *
     * @return RouteMatch
     */
    public function routeMatch()
    {
        if (!$this->routeMatch) {
            $this->routeMatch = Pi::engine()->application()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    /**
     * Get current request URI
     *
     * @param bool $encode
     *
     * @return string
     */
    public function requestUri($encode = false)
    {
        $uri = Pi::engine()->application()->getRequest()->getRequestUri();
        if ($uri && $encode) {
            $uri = $this->getView()->escapeUrl($uri);
        }

        return $uri;
    }
}
