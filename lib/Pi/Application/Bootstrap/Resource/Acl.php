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
use Pi\Acl\Acl as AclManager;
use Zend\Mvc\MvcEvent;

/**
 * ACL bootstrap resource
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Acl extends AbstractResource
{
    /** @var AclManager ACL handler */
    protected $aclHandler;

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Load global ACL handler
        $this->loadAcl();
        // Check access permission before any other action is performed
        $this->application->getEventManager()->attach(
            'dispatch',
            array($this, 'checkAccess'),
            9999
        );
    }

    /**
     * Load ACL handler
     *
     * @return void
     */
    protected function loadAcl()
    {
        $this->engine->bootResource('user');
        $this->aclHandler = new AclManager(
            $this->engine->section(),
            isset($this->options['default']) ? $this->options['default'] : null
        );
        $this->aclHandler->setRole(Pi::service('user')->getUser()->role());
        Pi::entity('acl', $this->aclHandler);
    }

    /**
     * Check if current access is allowed
     *
     * @param MvcEvent $e
     * @return bool
     */
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
                $denied = $this->aclHandler->checkAccess($resource)
                          ? false : true;
            // Check for managed components
            } elseif (
                in_array($route['controller'], $this->options['component'])) {
                $resource = array(
                    'name'  => $route['controller'],
                );
                if (!$this->aclHandler->checkAccess($resource)) {
                    $denied = true;
                } else {
                    $moduleName = $routeMatch->getParam('name')
                                  ?: $e->getRequest()->getPost('name');
                    if ($moduleName) {
                        $denied = false;
                        // Get allowed modules
                        $modulesAllowed =
                        Pi::registry('moduleperm')->read('manage');
                        // Denied if module is not allowed
                        if (null !== $modulesAllowed
                            && !in_array($moduleName, $modulesAllowed)
                        ) {
                            $denied = true;
                        }
                    // Denied if action page is not allowed
                    // if check on page is enabled
                    } elseif (!empty($this->options['check_page'])) {
                        if ($this->aclHandler->checkException($route)) {
                            $denied = false;
                        }
                    }
                }
            }
        }

        // Check for module access
        if (null === $denied) {
            // Get allowed modules
            $modulesAllowed = Pi::registry('moduleperm')->read($section);
            // Automatically allowed for not defined cases
            if (!isset($this->options['default'])
                || !empty($this->options['default'])
            ) {
                // Get all active modules
                $modulesActive = Pi::registry('modulelist')->read();
                // Denied if is explicitly not allowed installed modules
                if (isset($modulesActive[$route['module']])
                    && !in_array($route['module'], $modulesAllowed)
                ) {
                    $denied = true;
                }
            // Automatically denied for not defined cases
            } else {
                // Denied if module is not allowed
                if (null !== $modulesAllowed
                    && !in_array($route['module'], $modulesAllowed)
                ) {
                    $denied = true;
                }
            }
        }

        // Check for page access
        if (null === $denied) {
            // Denied if action page is not allowed if check on page is enabled
            if (!empty($this->options['check_page'])
                && 'dashboard' != $route['controller']
            ) {
                if ('admin' == $section
                    && $this->aclHandler->checkException($route)
                ) {
                    $denied = false;
                } elseif (!$this->aclHandler->checkAccess($route)) {
                    $denied = true;
                }
            }
        }

        // Jump to denied page upon denial
        if ($denied) {
            $statusCode = Pi::service('user')->getUser()->isGuest()
                          ? 401 : 403;
            $e->getResponse()->setStatusCode($statusCode);
            $e->setError(true);
        }
    }
}
