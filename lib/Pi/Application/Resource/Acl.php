<?php
/**
 * Bootstrap resource
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
 * @package         Pi\Application
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Resource;

use Pi;
use Pi\Acl\Acl as AclManager;
use Zend\Mvc\MvcEvent;

class Acl extends AbstractResource
{
    protected $aclHandler;

    /**
     * @return void
     */
    public function boot()
    {
        // Load user role
        //Pi::registry('user')->loadRole();
        // Load global ACL handler
        $this->loadAcl();
        // Check access permission before any other action is performed
        $this->application->getEventManager()->attach('dispatch', array($this, 'checkAccess'), 9999);
    }

    protected function loadAcl()
    {
        $this->engine->loadResource('authentication');
        $this->aclHandler = new AclManager($this->engine->section(), isset($this->options['default']) ? $this->options['default'] : null);
        $this->aclHandler->setRole(Pi::registry('user')->role());
        Pi::registry('acl', $this->aclHandler);
    }

    public function checkAccess(MvcEvent $e)
    {
        $denied = false;
        $section = $this->engine->section();
        $routeMatch = $e->getRouteMatch();
        $route = array(
            'module'        => $routeMatch->getParam('module'),
            'controller'    => $routeMatch->getParam('controller'),
            'action'        => $routeMatch->getparam('action')
        );
        $this->aclHandler->setModule($route['module']);

        // Check for admin dashboard access, which requires loosen permissions
        if ('admin' == $section && 'system' == $route['module'] && ('dashboard' == $route['controller'] || 'index' == $route['controller'])) {
            // Denied if system dashboard is not allowed
            $resource = array(
                'name'  => 'admin',
            );
            if (!$this->aclHandler->checkAccess($resource)) {
                $denied = true;
            }
        } else {
            // Get allowed modules
            $modulesAllowed = Pi::service('registry')->moduleperm->read($section);
            // Denied if module is not allowed
            if (null !== $modulesAllowed && !in_array($route['module'], $modulesAllowed)) {
                $denied = true;
            // Denied if action page is not allowed if check on page is enabled
            } elseif (!empty($this->options['check_page'])) {
                $resource = $route;
                if (!$this->aclHandler->checkAccess($resource)) {
                    $denied = true;
                }
            }
        }

        // Jump to denied page upon denial
        if ($denied) {
            $statusCode = Pi::registry('user')->isGuest() ? 401 : 403;
            $e->getResponse()->setStatusCode($statusCode);
            $e->setError('__denied__');
        }
    }
}
