<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractController;

/**
 * ACL bootstrap resource
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Permission extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Boot user resource
        $this->engine->bootResource('user');

        $events = $this->application->getEventManager();
        // Check access permission before any other action is performed
        $events->attach(
            MvcEvent::EVENT_DISPATCH,
            array($this, 'checkModule'),
            9999
        );

        // Setup action cache strategy
        $sharedEvents = $events->getSharedManager();
        // Attach listeners to controller
        $sharedEvents->attach(
            'PI_CONTROLLER',
            MvcEvent::EVENT_DISPATCH,
            array($this, 'test'),
            99999
        );
    }

    public function checkAction(MvcEvent $e)
    {
        //d(__METHOD__);
        // Skip cache if error occurred
        if ($e->isError()) {
            return;
        }
        if (empty($this->options['check_page'])) {
            return;
        }
        $section = $this->engine->section();
        $routeMatch = $e->getRouteMatch();
        $route = array(
            'section'       => $section,
            'module'        => $routeMatch->getParam('module'),
            'controller'    => $routeMatch->getParam('controller'),
            'action'        => $routeMatch->getparam('action')
        );
        $controller = $e->getTarget();
        if ($controller instanceof AbstractController
            && method_exists($controller, 'permissionException')
        ) {
            $exceptions = $controller->permissionException();
            if ($exceptions && in_array($route['action'], $exceptions)) {
                return;
            }
        }
        $access = Pi::service('permission')->pagePermission($route);
        if (false === $access) {
            $this->denyAccess($e);
        }

        return;

    }

    /**
     * Check if current HTTP request is allowed
     *
     * @param MvcEvent $e
     * @return bool
     */
    public function checkModule(MvcEvent $e)
    {
        //d(__METHOD__);
        $module = $e->getRouteMatch()->getParam('module');
        $access = Pi::service('permission')->modulePermission($module);
        if (!$access) {
            $this->denyAccess($e);
        }

        return;
    }

    /**
     * Set denied error
     *
     * @param MvcEvent $e
     * @return void
     */
    protected function denyAccess(MvcEvent $e)
    {
        $statusCode = Pi::service('user')->getUser()->isGuest()
            ? 401 : 403;
        $e->getResponse()->setStatusCode($statusCode);
        $e->setError(true);
    }
}
