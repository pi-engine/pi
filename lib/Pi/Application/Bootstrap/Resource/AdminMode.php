<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Zend\Mvc\MvcEvent;

/**
 * Admin operation mode handling
 *
 * @see Pi\Application\Bootstrap\Resource\AdminMode
 * @see Pi\View\Helper\AdminNav
 * @see Module\System\Controller\Admin\PermController
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AdminMode extends AbstractResource
{
    /**
     * Admin operation mode
     */
    const MODE_ACCESS = 'access';
    /**
     * Admin manage mode
     */
    const MODE_ADMIN = 'admin';

    /**
     * Deployment mode
     */
    const MODE_DEPLOYMENT = 'deployment';

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        if ('admin' == $this->engine->section()) {
            // Check and set admin mode if not set yet
            $this->application->getEventManager()->attach(
                MvcEvent::EVENT_RENDER,
                array($this, 'setMode'),
                5
            );
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
            $mode       = static::MODE_ACCESS;

            $module     = $route->getParam('module');
            $controller = $route->getParam('controller');
            if ('system' == $module) {
                $controllerClass = 'Module\System\Controller\Admin\\'
                                 . ucfirst($controller) . 'Controller';
                /*
                 * @FIXME `is_subclass_of` does not call __autoload in case if first argument is an object.
                 *  If first argument is string, PHP will call __autoload.
                 *  However, it seems __autoload is not called although the first argument is string.
                 */
                if (class_exists('Module\System\Controller\ComponentController')
                    && is_subclass_of(
                        $controllerClass,
                        'Module\System\Controller\ComponentController'
                    )
                ) {
                    $mode = static::MODE_ADMIN;
                }
            }

            $_SESSION['PI_BACKOFFICE']['mode'] = $mode;
        } else {
            $_SESSION['PI_BACKOFFICE']['changed'] = 0;
        }
    }
}
