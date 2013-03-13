<?php
/**
 * System managed component controller
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
 * @since           3.0
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Mvc\MvcEvent;

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
            //Pi::service('session')->backoffice->module = $name;
            $_SESSION['PI_BACKOFFICE']['module'] = $name;
        }
        // Set component
        //Pi::service('session')->backoffice->component = $component;
        $_SESSION['PI_BACKOFFICE']['component'] = $component;

        return parent::onDispatch($e);
    }
}
