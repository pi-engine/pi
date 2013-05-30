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
    /**#@+
     * Operation modes
     *
     * @see Pi\Application\Resource\AdminMode
     * @see Pi\View\Helper\AdminNav
     * @see Module\System\Controller\Admin\PermController
     */
    /**
     * Admin operation mode
     */
    const MODE_ADMIN = 'admin';
    /**
     * Settings mode
     */
    const MODE_SETTING = 'manage';
    /**
     * Deployment mode
     */
    const MODE_DEPLOYMENT = 'deployment';
    /**#@-*/

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
        if (empty($_SESSION['PI_BACKOFFICE']['changed']) && $route) {
            $module     = $route->getParam('module');
            $controller = $route->getParam('controller');
            if ('system' == $module && in_array($controller, array('block', 'config', 'page', 'resource', 'event'))) {
                $mode = static::MODE_SETTING;
            } else {
                $mode = static::MODE_ADMIN;
            }
            $_SESSION['PI_BACKOFFICE']['mode'] = $mode;
        } else {
            $_SESSION['PI_BACKOFFICE']['changed'] = 0;
        }
    }
}
