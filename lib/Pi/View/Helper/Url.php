<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\Router\RouteStackInterface;
use Pi\Mvc\Router\RouteMatch;
use Zend\View\Helper\Url as ZendUrl;

/**
 * Helper for assembling URL with routes and parameters
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->url('home');
 *  $this->url('default', array('module' => 'demo', 'controller' => 'test');
 * ```
 *
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
        if (null === $this->router()) {
            throw new \RuntimeException(
                'No RouteStackInterface instance provided'
            );
        }

        if (!$name) {
            if ($this->routeMatch() === null) {
                throw new \RuntimeException('No RouteMatch instance provided');
            }

            $name = $this->routeMatch()->getMatchedRouteName();

            if ($name === null) {
                throw new \RuntimeException(
                    'RouteMatch does not contain a matched route name'
                );
            }
        }

        // Complete current module/controller
        if (!isset($params['module']) && isset($params['action'])) {
            $routeMatch = $this->routeMatch();
            $params['module'] = $routeMatch->getParam('module');
            if (!isset($params['controller'])) {
                $params['controller'] = $routeMatch->getParam('controller');
            }
        }

        if ($reuseMatchedParams && $this->routeMatch() !== null) {
            $params = array_merge($this->routeMatch()->getParams(), $params);
        }

        $options['name'] = $name;

        return $this->router()->assemble($params, $options);
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
}
