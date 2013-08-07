<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Http\PhpEnvironment\Request;
use Zend\Uri\Http as HttpUri;

/**
 * URL handling service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Url extends AbstractService
{
    /**
     * Router handler
     *
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * Set router
     *
     * @param RouteStackInterface $router
     * @return Url
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get router and load if not specified
     *
     * @return RouteStackInterface
     */
    public function getRouter()
    {
        if (!$this->router instanceof RouteStackInterface) {
            $this->router = Pi::engine()->application()->getRouter();
        }

        return $this->router;
    }

    /**
     * Generates an url given the name of a route.
     *
     * @see    Zend\Mvc\Router\RouteInterface::assemble()
     * @param  string  $route              Name of the route
     * @param  array   $params             Parameters for the link
     * @param  array   $options            Options for the route
     * @return string                   For the link href attribute
     */
    public function assemble(
        $route = null,
        array $params = array(),
        $options = array())
    {
        if (!$this->getRouter()) {
            throw new \RuntimeException(
                'No RouteStackInterface instance provided'
            );
        }
        $options['name'] = $route ?: 'default';

        return $this->getRouter()->assemble($params, $options);
    }

    /**
     * Match a URL against routes and parse to paramters
     *
     * Note: host is not checked for match
     *
     * @param string $url
     * @return RouteMatch|null
     */
    public function route($url)
    {
        if (!$this->getRouter()) {
            throw new \RuntimeException(
                'No RouteStackInterface instance provided'
            );
        }

        $uri = new HttpUri($url);
        $request = new Request();
        $request->setUri($uri);
        $result = $this->getRouter()->match($request);

        return $result;
    }
}
