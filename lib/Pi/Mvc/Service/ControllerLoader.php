<?php
/**
 * Controller loader
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

namespace Pi\Mvc\Service;

use Pi;
use Pi\Application\Engine\AbstractEngine;
use Pi\Mvc\Router\RouteMatch;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class ControllerLoader implements EventManagerAwareInterface, ServiceManagerAwareInterface
{
    protected $eventManager;
    protected $serviceManager;

    /**
     * Load module controller with parameters
     *
     * @param string|array|RouteMatch $params
     * @return AbstractActionController
     */
    public function get($params)
    {
        // Controller name is passed, get params from RouteMatch
        if (is_string($params)) {
            $routeMatch = $this->serviceManager->get('Application')->getRouteMatch();
        } elseif ($params instanceof RouteMatch) {
            $routeMatch = $params;
        } else {
            $routeMatch = null;
        }

        if ($routeMatch) {
            $params = array(
                'section'       => $this->serviceManager->get('Application')->getSection(),
                'module'        => $routeMatch->getParam('module'),
                'controller'    => $routeMatch->getParam('controller'),
            );
        }

        if (!Pi::service('module')->isActive($params['module'])) {
            throw new ServiceNotCreatedException('Module is not found.');
        }
        $directory = Pi::service('module')->directory($params['module']);

        // Look up controller class in module folder
        $controllerClass = sprintf('Module\\%s\\Controller\\%s\\%sController', ucfirst($directory), ucfirst($params['section']), ucfirst($params['controller']));
        // Look up in system's shared admin controller folder for admin controller if not found in module fodler
        if (!class_exists($controllerClass) && AbstractEngine::ADMIN == $params['section']) {
            $controllerClass = sprintf('Module\\System\\Controller\\Module\\%sController', ucfirst($params['controller']));
        }
        if (!class_exists($controllerClass)) {
            throw new ServiceNotCreatedException('Controller class is not found.');
        }
        $controller = new $controllerClass;
        if ($controller instanceof EventManagerAwareInterface) {
            $controller->setEventManager($this->eventManager);
        }
        if ($controller instanceof ServiceLocatorAwareInterface) {
            $controller->setServiceLocator($this->serviceManager);
        }

        if (method_exists($controller, 'setPluginManager')) {
            $controller->setPluginManager($this->serviceManager->get('ControllerPluginManager'));
        }

        return $controller;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return AbstractActionController
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->eventManager = $events;
        return $this;
    }
    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Set locator instance
     *
     * @param  ServiceLocatorInterface $locator
     * @return AbstractActionController
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
