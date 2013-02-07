<?php
/**
 * Controller manager class
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
 * @package         Pi\Mvc
 * @version         $Id$
 */
namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\ControllerManager as ZendControllerManager;

class ControllerManager extends ZendControllerManager
{
    /**
     * Canonicalize name
     *
     * @param  string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        static $inCanonicalization = false;

        if ($inCanonicalization) {
            $inCanonicalization = false;
            return $name;
        }

        $invokableClass = null;
        if (false === strpos($name, '\\')) {
            $routeMatch = $this->serviceLocator->get('Application')->getRouteMatch();
            if ($routeMatch) {
                $params = array(
                    'section'       => $this->serviceLocator->get('Application')->getSection(),
                    'module'        => $routeMatch->getParam('module'),
                    'controller'    => $routeMatch->getParam('controller'),
                );
                $directory = Pi::service('module')->directory($params['module']) ?: $params['module'];

                // Look up controller class in module folder
                $invokableClass = sprintf('Module\\%s\\Controller\\%s\\%sController', ucfirst($directory), ucfirst($params['section']), ucfirst($params['controller']));
                // Look up in system's shared admin controller folder for admin controller if not found in module fodler
                if (!class_exists($invokableClass) && 'admin' == $params['section']) {
                    $invokableClass = sprintf('Module\\System\\Controller\\Module\\%sController', ucfirst($params['controller']));
                }
                $name = $invokableClass;
            }
        }

        $cName = parent::canonicalizeName($name);

        if ($invokableClass && !isset($this->invokableClasses[$cName]) && class_exists($invokableClass)) {
            $inCanonicalization = true;
            $this->setInvokableClass($cName, $invokableClass);
            $inCanonicalization = false;
        }

        return $cName;
    }
}