<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System;

use Pi;
use Pi\Application\Bootstrap\Resource\AdminMode;

/**
 * Module admin menu handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Menu
{
    /**
     * Get admin mode list
     *
     * @param null|string $mode
     *
     * @return array
     */
    public static function modes($mode = null)
    {
        $modes = array(
            array(
                'name'  => AdminMode::MODE_ACCESS,
                'label' => _a('Operation', 'system:admin'),
                'icon'  => 'fa fa-wrench',
            ),
            array(
                'name'  => AdminMode::MODE_ADMIN,
                'label' => _a('Setting', 'system:admin'),
                'icon'  => 'fa fa-cogs',
            ),
            array(
                'name'  => AdminMode::MODE_DEPLOYMENT,
                'label' => _a('Deployment', 'system:admin'),
                'icon'  => 'fa fa-cloud-upload',
                'link'  => '',
            ),
        );
        if (AdminMode::MODE_ADMIN == $mode) {
            $module = Pi::service('url')->getRouteMatch()->getParam('name');
        } else {
            $module = Pi::service('module')->current() ?: 'system';
        }
        array_walk($modes, function (&$config) use ($mode, $module) {
            $config['active'] = ($mode == $config['name']) ? 1 : 0;
            if (!isset($config['link'])) {
                $config['link'] = Pi::service('url')->assemble('admin', array(
                    'module'        => 'system',
                    'controller'    => 'dashboard',
                    'action'        => 'mode',
                    'mode'          => $config['name'],
                ), array('query' => array('name' => $module)));
            }
        });

        return $modes;
    }

    /**
     * Load side main menu for operations
     *
     * @param string $module
     *
     * @return array
     */
    public static function mainOperation($module)
    {
        $mode = AdminMode::MODE_ACCESS;

        $linkCallback = function ($name) {
            return Pi::service('url')->assemble('admin', array(
                'module'        => $name,
                'controller'    => 'index',
                'action'        => 'index',
            ));
        };
        $categories = static::getCategories($mode, $module, $linkCallback);

        return $categories;
    }

    /**
     * Get side main menu for management
     *
     * @param string $module
     * @param string $component
     *
     * @return array
     */
    public static function mainComponent($module, $component)
    {
        $mode   = AdminMode::MODE_ADMIN;

        $linkCallback = function ($name) use ($component) {
            return Pi::service('url')->assemble('admin', array(
                'module'        => 'system',
                'controller'    => $component,
                'name'          => $name,
            ));
        };
        $categories = static::getCategories($mode, $module, $linkCallback);

        return $categories;
    }

    /**
     * Load module component sub menu
     *
     * @param string $module
     * @param array $options
     *
     * @return string
     */
    public static function subComponent($module, array $options = array())
    {
        $mode = AdminMode::MODE_ADMIN;
        $modulesAllowed = Pi::service('permission')->moduleList($mode);
        if (!in_array($module, $modulesAllowed)) {
            $content = '';
        } else {
            $navConfig = Pi::registry('navigation')->read('system-component');
            /*
            //@FIXME The following assignment will break navigation highlight, thus remove it.
            foreach ($navConfig as $key => &$nav) {
                $nav['params']['name'] = $module;
            }
            */
            $helper     = Pi::service('view')->getHelper('navigation');
            $navigation = $helper($navConfig);
            if (!isset($options['ulClass'])) {
                $options['ulClass'] = 'nav nav-tabs';
            }
            $content = $navigation->menu()->renderMenu(null, $options);
        }

        return $content;
    }

    /**
     * Load module admin sub menu
     *
     * @param string $module
     * @param array $options
     *
     * @return string[]
     */
    public static function subOperation($module, array $options = array())
    {
        $mode = AdminMode::MODE_ACCESS;
        $modulesAllowed = Pi::service('permission')->moduleList($mode);
        if (!in_array($module, $modulesAllowed)) {
            $content = '';
        } else {
            $helper = Pi::service('view')->getHelper('navigation');
            $navigation = $helper(
                $module . '-admin',
                array('section' => 'admin')
            );
            $options['maxDepth'] = 0;
            if (!isset($options['ulClass'])) {
                $options['ulClass'] = 'nav';
            }
            if (!empty($options['sub'])) {
                $content = $navigation->menu()->renderPair(null, $options);
            } else {
                $content = $navigation->menu()->renderMenu(null, $options);
            }
        }

        return $content;
    }

    /**
     * Get categorized modules
     *
     * @param string $mode
     * @param string $module
     * @param Closure $linkCallback
     *
     * @return array
     */
    protected static function getCategories($mode, $module, $linkCallback)
    {
        $categories     = Pi::registry('category', 'system')->read();
        $moduleList     = Pi::registry('modulelist')->read();
        $modulesAllowed = Pi::service('permission')->moduleList($mode);
        foreach (array_keys($moduleList) as $name) {
            // Filter restricted modules
            if (!in_array($name, $modulesAllowed)) {
                unset($moduleList[$name]);
                continue;
            }
            // Build module meta
            $moduleList[$name] = array(
                'name'      => $name,
                'label'     => $moduleList[$name]['title'],
                'icon'      => $moduleList[$name]['icon'],
                'active'    => $name == $module ? 1 : 0,
                'href'      => call_user_func($linkCallback, $name),
            );

        }

        if (isset($moduleList['system'])) {
            $category = array(
                'title'     => '',
                'icon'      => '',
                'modules'   => array('system'),
            );
            array_unshift($categories, $category);
        }

        // Categorize modules
        array_walk($categories, function (&$category) use (&$moduleList) {
            $category['label'] = $category['title'];
            $modules = (array) $category['modules'];
            $category['modules'] = array();
            foreach ($modules as $name) {
                if (isset($moduleList[$name])) {
                    $category['modules'][] = $moduleList[$name];
                    unset($moduleList[$name]);
                }
            }

        });

        // Collect un-categorized modules
        if ($moduleList) {
            $categories[] = array(
                'label'     => __('Uncategorized'),
                'icon'      => '',
                'modules'   => array_values($moduleList),
            );
        }

        return $categories;
    }
}