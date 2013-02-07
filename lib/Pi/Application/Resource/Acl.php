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
        $denied = null;
        $section = $this->engine->section();
        $routeMatch = $e->getRouteMatch();
        $route = array(
            'section'       => $section,
            'module'        => $routeMatch->getParam('module'),
            'controller'    => $routeMatch->getParam('controller'),
            'action'        => $routeMatch->getparam('action')
        );
        $this->aclHandler->setModule($route['module']);

        // Check for admin access, which requires loosen permissions
        if ('admin' == $section && 'system' == $route['module']) {
            //$denied = false;
            // Check for admin entries
            if (in_array($route['controller'], $this->options['entry'])) {
                $resource = array(
                    'name'  => 'admin',
                );
                $denied = $this->aclHandler->checkAccess($resource) ? false : true;
            // Check for managed components
            } elseif (in_array($route['controller'], $this->options['component'])) {
                $resource = array(
                    'name'  => $route['controller'],
                );
                if (!$this->aclHandler->checkAccess($resource)) {
                    $denied = true;
                } else {
                    $moduleName = $routeMatch->getParam('name') ?: $e->getRequest()->getPost('name');
                    if ($moduleName) {
                        $denied = false;
                        // Get allowed modules
                        $modulesAllowed = Pi::service('registry')->moduleperm->read('manage');
                        // Denied if module is not allowed
                        if (null !== $modulesAllowed && !in_array($moduleName, $modulesAllowed)) {
                            $denied = true;
                        }
                    // Denied if action page is not allowed if check on page is enabled
                    } elseif (!empty($this->options['check_page'])) {
                        if ($this->aclHandler->checkException($route)) {
                            $denied = false;
                        }
                    }
                }
            }
        }

        // Check for module and page access
        if (null === $denied) {
            $denied = false;

            // Get allowed modules
            $modulesAllowed = Pi::service('registry')->moduleperm->read($section);

            // Denied if module is not allowed
            if (null !== $modulesAllowed && !in_array($route['module'], $modulesAllowed)) {
                $denied = true;
            // Denied if action page is not allowed if check on page is enabled
            } elseif (!empty($this->options['check_page']) && 'dashboard' != $route['controller']) {
                if ('admin' == $section && $this->aclHandler->checkException($route)) {
                    $denied = false;
                } elseif (!$this->aclHandler->checkAccess($route)) {
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
