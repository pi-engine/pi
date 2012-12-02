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
        Pi::registry('user')->loadRole();
        // Load global ACL handler
        $this->loadAcl();
        // Check access permission before any other action is performed
        $this->application->getEventManager()->attach('dispatch', array($this, 'checkAccess'), 9999);
    }

    protected function loadAcl()
    {
        $this->engine->loadResource('authentication');
        $this->aclHandler = new AclManager($this->engine->section(), isset($this->options['default']) ? $this->options['default'] : null);
        $this->aclHandler->setRole(Pi::registry('user')->role);
        Pi::registry('acl', $this->aclHandler);
    }

    public function checkAccess(MvcEvent $e)
    {
        $route = $e->getRouteMatch();
        $resource = array(
            'module'        => $route->getParam('module'),
            'controller'    => $route->getParam('controller'),
            'action'        => $route->getparam('action')
        );

        // All non-admin actions in system module are public
        if ($resource['module'] == 'system' && $this->engine->section() != 'admin') {
            return true;
        }

        if (!$this->aclHandler->checkAccess($resource)) {
            /**#@+
             * Custom hack for EEFOCUS
             */
            if (!Pi::registry('user')->isGuest()) {
                if (in_array($resource['module'], array('article', 'widget'))) {
                    return true;
                }
            }
            /**#@-*/

            $statusCode = Pi::registry('user')->isGuest() ? 401 : 403;
            $e->getResponse()->setStatusCode($statusCode);
            $e->setError('__denied__');
        }
    }
}
