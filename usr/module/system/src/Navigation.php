<?php
/**
 * Pi system navigation content generator
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
 * @version         $Id$
 */

namespace Module\System;

use Pi;

class Navigation
{
    public static function front($module)
    {
        $nav = array(
            'parent' => array(),
        );

        $modules = Pi::service('registry')->modulelist->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $node = Pi::service('registry')->navigation->read($key . '-front');
            if (!is_array($node)) {
                continue;
            }

            $nav['parent'][$key] = array(
                'label'     => $data['title'],
                'route'     => 'default',
                'module'    => $key,
                'pages'     => $node,
            );
        }
        if (empty($nav['parent'])) {
            $nav['visible'] = 0;
        }

        return $nav;
    }

    public static function admin($module)
    {
        $pages = array();
        /*
        $name = 'system';
        $nav = array(
            'route'     => 'admin',
            'module'    => &$name,
            'pages'     => &$pages,
        );
        */
        $nav = array(
            'parent' => &$pages,
        );

        $modules = Pi::service('registry')->modulelist->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $pages[$key] = array(
                'label'     => $data['title'],
                'module'    => $key,
                'route'     => 'admin',
            );
        }
        /*
        if ($pages) {
            $list = array_keys($pages);
            $name = array_shift($list);
        }
        */

        return $nav;
    }

    public static function config($module)
    {
        $pages = array();
        /*
        $name = 'system';
        $nav = array(
            'params'    => array(
                'name'  => &$name,
            ),
            'pages'     => &$pages,
        );
        */
        $nav = array(
            'parent' => &$pages,
        );

        $model = Pi::model('config');
        $select = $model->select()->group('module')->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $configCounts = array();
        foreach ($rowset as $row) {
            $configCounts[$row->module] = $row->count;
        }

        $modules = Pi::service('registry')->modulelist->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            if (!empty($configCounts[$key])) {
                $pages[$key] = array(
                    'label'         => $data['title'],
                    'module'        => $module,
                    'route'         => 'admin',
                    'controller'    => 'config',
                    'action'        => 'module',
                    'params'        => array(
                        'name'  => $key,
                    ),
                );
            }
        }
        /*
        if ($pages) {
            $list = array_keys($pages);
            $name = array_shift($list);
        }
        */

        return $nav;
    }

    public static function block($module)
    {
        $pages = array();
        $nav = array(
            'pages'     => &$pages,
        );

        /*
        $pages[''] = array(
            'label'         => __('Custom blocks'),
            'module'        => $module,
            'route'         => 'admin',
            'controller'    => 'block',
            'pages'         => array(
                'add'   => array(
                    'label' => __('Add custom block'),
                    'module' => 'system',
                    'route' => 'admin',
                    'controller' => 'block',
                    'action' => 'add',
                    'visible' => 0,
                ),
                'custom'  => array(
                    'label' => __('Edit custom block'),
                    'module' => 'system',
                    'route' => 'admin',
                    'controller' => 'block',
                    'action' => 'editcustom',
                    'visible' => 0,
                ),
            ),
        );
        $pages['_divider'] = array();
        */

        $model = Pi::model('block');
        $select = $model->select()->group('module')->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $blockCounts = array();
        foreach ($rowset as $row) {
            $blockCounts[$row->module] = $row->count;
        }

        $modules = Pi::service('registry')->modulelist->read('active');
        foreach ($modules as $key => $data) {
            if (empty($blockCounts[$key])) {
                continue;
            }
            $pages[$key] = array(
                'label'         => $data['title'],
                'module'        => $module,
                'route'         => 'admin',
                'controller'    => 'block',
                'params'        => array(
                    'name'  => $key,
                ),
                'pages'         => array(
                    'edit'  => array(
                        'label' => __('Edit a block'),
                        'module' => 'system',
                        'route' => 'admin',
                        'controller' => 'block',
                        'action' => 'edit',
                        'visible' => 0,
                        'params'    => array(
                            'name'  => $key,
                        ),
                    ),
                    'clone' => array(
                        'label' => __('Clone a block'),
                        'module' => 'system',
                        'route' => 'admin',
                        'controller' => 'block',
                        'action' => 'clone',
                        'visible' => 0,
                        'params'    => array(
                            'name'  => $key,
                        ),
                    ),
                ),
            );
        }

        return $nav;
    }

    public static function page($module)
    {
        $pages = array();
        $nav = array(
            'pages'     => &$pages,
        );

        $modules = Pi::service('registry')->modulelist->read('active');
        $systemModule = $modules['system'];
        unset($modules['system']);
        $modules = array('system' => $systemModule) + $modules;

        foreach ($modules as $key => $data) {
            $pages[$key] = array(
                'label'         => $data['title'],
                'module'        => $module,
                'route'         => 'admin',
                'controller'    => 'page',
                'params'        => array(
                    'name'  => $key,
                ),
                'pages'         => array(
                    'layout'    => array(
                        'label'         => __('Block layout'),
                        'module'        => 'system',
                        'route'         => 'admin',
                        'controller'    => 'page',
                        'action'        => 'block',
                        'visible'       => 0,
                        'params'        => array(
                            'name'  => $key,
                        ),
                    ),
                    'add'   => array(
                        'label' => __('Setup page'),
                        'module' => 'system',
                        'route' => 'admin',
                        'controller' => 'page',
                        'action' => 'add',
                        'visible' => 0,
                        'params'    => array(
                            'name'  => $key,
                        ),
                    ),
                ),
            );
        }

        return $nav;
    }
}
