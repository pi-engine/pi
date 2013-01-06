<?php
/**
 * Back Office side menu helper
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
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Pi\Acl\Acl;
use Zend\View\Helper\AbstractHelper;

class AdminSide extends AbstractHelper
{
    /**
     * Get back office side menu
     *
     * @param string $module
     * @return string
     */
    public function __invoke($module = 'system')
    {
        $mode = 'manage';
        if ('operation' == Pi::service('session')->backoffice->mode) {
            $mode = 'operation';
        }
        $modules = Pi::service('registry')->modulelist->read();
        $modulesAllowed = Pi::service('registry')->moduleperm->read('admin');
        if (null === $modulesAllowed || !is_array($modulesAllowed)) {
            $modulesAllowed = array_keys($modules);
        }
        $navConfig = array();

        // Get manage mode navigation
        if ('manage' == $mode && 'system' == $module && in_array($module, $modulesAllowed)) {
            //$modulesAllowed = Pi::registry('user')->isAdmin() ? null : Pi::service('registry')->moduleperm->read('manage');
            $managedAllowed = Pi::service('registry')->moduleperm->read('manage');

            $routeMatch = Pi::engine()->application()->getRouteMatch();
            $params = $routeMatch->getParams();
            if (empty($params['name'])) {
                $params['name'] = 'system';
            }
            // Build managed navigation for all modules
            // Shall be limited?
            foreach ($modules as $name => $item) {
                if (is_array($managedAllowed) && !in_array($name, $managedAllowed)) {
                    continue;
                }
                $navConfig[$name] = array(
                    'label'         => $item['title'],
                    'route'         => 'admin',
                    'module'        => $params['module'],
                    'controller'    => $params['controller'],
                    //'action'        => $params['action'],
                    'params'        => array(
                        'name'          => $name,
                    ),
                    'active'        => $name == $params['name'] ? 1 : 0,
                );
            }

        // Get operation mode navigation
        } elseif ('operation' == $mode) {
            // Build the navigation
            foreach ($modules as $name => $item) {
                if (!in_array($name, $modulesAllowed)) {
                    continue;
                }
                $navConfig[$name] = array(
                    'label'         => $item['title'],
                    'route'         => 'admin',
                    'module'        => $name,
                    'active'        => $name == $module ? 1 : 0,
                );
            }
        }

        $navigation = $this->view->navigation($navConfig);
        return $navigation;
    }

}
