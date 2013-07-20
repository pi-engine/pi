<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Pi\Acl\Acl as AclManager;
use Zend\Mvc\MvcEvent;

/**
 * Operion mode
 *
 * @see Pi\Application\Bootstrap\Resource\AdminMode
 * @see Pi\View\Helper\AdminNav
 * @see Module\System\Controller\Admin\PermController
 */
class Adminmode extends AbstractResource
{
    /**#@+
     * Operation modes
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
     * {@inheritDoc}
     */
    public function boot()
    {
        if ('admin' == $this->engine->section()) {
            // Check and set admin mode if not set yet
            $this->application->getEventManager()->attach(MvcEvent::EVENT_RENDER, array($this, 'setMode'), 5);
        }
    }

    /**
     * Set operation mode
     *
     * @param MvcEvent $e
     * @return void
     */
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
