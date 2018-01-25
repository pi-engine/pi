<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Pi\Mvc\Router\RouteMatch as RouteMatchHandler;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for loading parameters from routeMatch
 *
 * - Usage inside a phtml template
 *
 * ```
 *  $module = $this->routeMatch('module');
 *  $param  = $this->routeMatch('param');
 *  $routeMatch  = $this->routeMatch();
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RouteMatch extends AbstractHelper
{
    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatchHandler
     */
    protected $routeMatch;

    /**
     * Get params
     *
     * {@inheritdoc}
     */
    public function __invoke($name = null)
    {
        $routeMatch = Pi::service('url')->getRouteMatch();
        if (!func_num_args()) {
            return $routeMatch;
        }

        return $routeMatch->getParam($name);
    }
}
