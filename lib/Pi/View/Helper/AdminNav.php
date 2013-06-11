<?php
/**
 * Back Office navigation helper
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
use Pi\Application\Resource\AdminMode;
use Zend\View\Helper\AbstractHelper;

class AdminNav extends AbstractHelper
{
    protected $module;
    protected $side;
    protected $top;

    public function __invoke($module = 'system')
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Get back office run mode list
     *
     * @param string $module
     * @return string
     */
    public function modes($module = null)
    {
        //$mode = Pi::service('session')->backoffice->mode;
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $modules = Pi::service('registry')->modulelist->read();
        $moduleList = array_keys($modules);
        $modes = array(
            AdminMode::MODE_ADMIN      => array(
                'label' => __('Operation'),
                'link'  => '',
            ),
            AdminMode::MODE_SETTING    => array(
                'label' => __('Setting'),
                'link'  => '',
            ),
            AdminMode::MODE_DEPLOYMENT => array(
                'label' => __('Deployment'),
                'link'  => '',
            ),
        );
        if (isset($modes[$mode])) {
            $modes[$mode]['active'] = 1;
        }
        foreach (array(AdminMode::MODE_ADMIN, AdminMode::MODE_SETTING) as $type) {
            $allowed = Pi::service('registry')->moduleperm->read($type);
            if (null === $allowed || !is_array($allowed)) {
                $allowed = $moduleList;
            } else {
                $allowed = array_intersect($allowed, $moduleList);
            }
            if ($allowed) {
                /**#@+
                 * Check access permission to managed components
                 */
                if (AdminMode::MODE_SETTING == $type) {
                    $navConfig = Pi::service('registry')->navigation->read('system-component') ?: array();
                    if (!$navConfig) {
                        continue;
                    }
                    $navIsEmpty = true;
                    foreach ($navConfig as $key => $page) {
                        if (!isset($page['visible']) || $page['visible']) {
                            $navIsEmpty = false;
                            break;
                        }
                    }
                    if ($navIsEmpty) {
                        continue;
                    }
                }
                /**#@-*/
                $modes[$type]['link'] = $this->view->url('admin', array(
                    'module'        => 'system',
                    'controller'    => 'dashboard',
                    'action'        => 'mode',
                    'mode'          => $type,
                ));
            }
        }

        return $modes;
    }

    /**
     * Get back office side menu
     *
     * @param string $module
     * @return string
     */
    public function side($module = null)
    {
        if (null !== $this->side) {
            return $this->side;
        }

        $module = $module ?: $this->module;
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $modules = Pi::service('registry')->modulelist->read();
        $navConfig = array();

        $navigation = '';
        // Get manage mode navigation
        if (AdminMode::MODE_SETTING == $mode && 'system' == $module/* && in_array($module, $modulesAllowed)*/) {
            $managedAllowed = Pi::service('registry')->moduleperm->read($mode);

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

            $navigation = $this->view->navigation($navConfig);
        // Get operation mode navigation
        } elseif (AdminMode::MODE_ADMIN == $mode) {
            $adminAllowed = Pi::service('registry')->moduleperm->read($mode);
            if (null === $adminAllowed || !is_array($adminAllowed)) {
                $adminAllowed = array_keys($modules);
            }

            // Build the navigation
            foreach ($modules as $name => $item) {
                if (!in_array($name, $adminAllowed)) {
                    continue;
                }
                $config = array(
                    'label'         => $item['title'],
                    'route'         => 'admin',
                    'module'        => $name,
                    'controller'    => 'dashboard',
                    //'action'        => 'module',
                    'active'        => $name == $module ? 1 : 0,
                );
                /*
                if ('system' == $name) {
                    $config['params'] = array(
                        'mode'  => 'admin',
                    );
                }
                */
                $navConfig[$name] = $config;
            }
            $navigation = $this->view->navigation($navConfig);
        }

        //$navigation = $this->view->navigation($navConfig);
        $this->side = $navigation;

        return $navigation;
    }

    /**
     * Get back office top menu
     *
     * @param string $module
     * @return string
     */
    public function top($module = null)
    {
        if (null !== $this->top) {
            return $this->top;
        }

        $module = $module ?: $this->module;
        $mode = $_SESSION['PI_BACKOFFICE']['mode'];

        $navigation = '';
        // Managed components
        if (AdminMode::MODE_SETTING == $mode && 'system' == $module) {
            $navConfig = Pi::service('registry')->navigation->read('system-component') ?: array();
            $currentModule = $_SESSION['PI_BACKOFFICE']['module'];
            if ($currentModule) {
                foreach ($navConfig as $key => &$nav) {
                    $nav['params']['name'] = $currentModule;
                }
            }
            $navigation = $this->view->navigation($navConfig);
        // Module operations
        } elseif (AdminMode::MODE_ADMIN == $mode) {
            $modulesAllowed = Pi::service('registry')->moduleperm->read($mode);
            if (null === $modulesAllowed || in_array($module, $modulesAllowed)) {
                $navigation = $this->view->navigation($module . '-admin', array('section' => 'admin'));
            } else {
                $navigation = $this->view->navigation(array());
            }
        }

        $this->top = $navigation;

        return $navigation;
    }
}
