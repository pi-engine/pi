<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\ControllerManager as ZendControllerManager;

/**
 * Controller load manager
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
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
            $application = $this->serviceLocator->get('Application');
            $routeMatch = $application->getRouteMatch();
            if ($routeMatch) {
                $module = $routeMatch->getParam('module');
                // Only active module controller are accessible
                if (!Pi::service('module')->isActive($module)) {
                    return '';
                }
                $params = array(
                    'section'       => $application->getSection(),
                    'module'        => $module,
                    'controller'    => $routeMatch->getParam('controller'),
                );
                $directory = Pi::service('module')->directory($module)
                    ?: $module;

                // Look up controller class in module folder
                $invokableClass = sprintf(
                    'Module\\%s\Controller\\%s\\%sController',
                    ucfirst($directory),
                    ucfirst($params['section']),
                    ucfirst($params['controller'])
                );
                // Look up in system's shared admin controller folder
                // for admin controller if not found in module fodler
                if (!class_exists($invokableClass)
                    && 'admin' == $params['section']
                ) {
                    $invokableClass = sprintf(
                        'Module\System\Controller\Module\\%sController',
                        ucfirst($params['controller'])
                    );
                }
                $name = $invokableClass;
            }
        }

        $cName = parent::canonicalizeName($name);

        if ($invokableClass
            && !isset($this->invokableClasses[$cName])
            && class_exists($invokableClass)
        ) {
            $inCanonicalization = true;
            $this->setInvokableClass($cName, $invokableClass);
            $inCanonicalization = false;
        }

        return $cName;
    }
}
