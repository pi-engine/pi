<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller;

use Pi;
use Pi\Mvc\Controller\ActionController;
//use Zend\Mvc\MvcEvent;

/**
 * System admin component controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ComponentController extends ActionController
{
    /**
     * Get current module name
     *
     * @param string|null $default
     *
     * @return null|string
     */
    protected function moduleName($default = null)
    {
        if (isset($_SESSION['PI_BACKOFFICE']['module'])) {
            $module = $_SESSION['PI_BACKOFFICE']['module'];
        } else {
            $module = $default;
        }

        return $module;
    }

    /**
     * {@inheritDoc}
     */
    protected function preAction($e)
    {
        $routeMatch = $e->getRouteMatch();
        $name       = $routeMatch->getParam('name');
        $component  = $routeMatch->getParam('controller');

        // Settings for admin navigation
        // Set module
        if (!empty($name)) {
            $_SESSION['PI_BACKOFFICE']['module'] = $name;
        }

        // Set component
        $_SESSION['PI_BACKOFFICE']['component'] = $component;

        // Load translations
        Pi::service('i18n')->load('module/' . $name . ':default');
        Pi::service('i18n')->load('module/' . $name . ':admin');
    }

    /**
     * Check permission
     *
     * @param string $module
     * @param string $op
     *
     * @return bool
     */
    protected function permission($module, $op)
    {
        if (Pi::service('permission')->isAdmin($module)) {
            return true;
        }

        $result = Pi::service('permission')->modulePermission($module, 'admin');
        if ($result) {
            $result = Pi::service('permission')->hasPermission(array(
                'module'    => 'system',
                'resource'  => $op
            ));
        }
        if (!$result) {
            $this->terminate('Access denied.');
        }

        return $result;
    }
}
