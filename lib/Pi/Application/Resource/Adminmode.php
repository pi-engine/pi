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

class Adminmode extends AbstractResource
{
    /**
     * @return void
     */
    public function boot()
    {
        if ('admin' == $this->engine->section()) {
            // Check and set admin mode if not set yet
            $this->application->getEventManager()->attach(MvcEvent::EVENT_RENDER, array($this, 'setMode'), 5);
        }
    }

    public function setMode(MvcEvent $e)
    {
        $route = $e->getRouteMatch();
        //d(Pi::service('session')->backoffice->changed);
        //if (!Pi::service('session')->backoffice->changed && $route) {
        if (empty($_SESSION['PI_BACKOFFICE']['changed']) && $route) {
            $module     = $route->getParam('module');
            $controller = $route->getParam('controller');
            if ('system' == $module && in_array($controller, array('block', 'config', 'page', 'resource', 'event'))) {
                $mode = 'manage';
            } else {
                $mode = 'admin';
            }
            //Pi::service('session')->backoffice->mode = $mode;
            $_SESSION['PI_BACKOFFICE']['mode'] = $mode;
        } else {
            //Pi::service('session')->backoffice->changed = 0;
            $_SESSION['PI_BACKOFFICE']['changed'] = 0;
        }
    }
}
