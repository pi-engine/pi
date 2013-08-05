<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Mvc\MvcEvent;

/**
 * System admin component controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ComponentController extends ActionController
{
    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws \DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $name = $routeMatch->getParam('name');
        $component = $routeMatch->getParam('controller');
        // Set module
        if (!empty($name)) {
            $_SESSION['PI_BACKOFFICE']['module'] = $name;
        }
        // Set component
        $_SESSION['PI_BACKOFFICE']['component'] = $component;

        return parent::onDispatch($e);
    }
}
