<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System;

use Pi;
use Zend\Db\Sql\Expression;

/**
 * Navigation handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Navigation
{
    /**
     * Front navigation
     *
     * @param string $module
     * @return array
     */
    public static function front($module)
    {
        $nav = array(
            'parent' => array(),
        );

        $modules = Pi::registry('modulelist')->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $node = Pi::registry('navigation')->read($key . '-front');
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

    /**
     * Admin navigation
     *
     * @param string $module
     * @return array
     */
    public static function admin($module)
    {
        $pages = array();
        $nav = array(
            'parent' => &$pages,
        );

        $modules = Pi::registry('modulelist')->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $pages[$key] = array(
                'label'     => $data['title'],
                'module'    => $key,
                'route'     => 'admin',
            );
        }

        return $nav;
    }

    /**
     * Navigation for configs
     *
     * @param string $module
     * @return array
     */
    public static function config($module)
    {
        $pages = array();
        $nav = array(
            'parent' => &$pages,
        );

        $model = Pi::model('config');
        $select = $model->select()->group('module')
            ->columns(array('count' => new Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $configCounts = array();
        foreach ($rowset as $row) {
            $configCounts[$row->module] = $row->count;
        }

        $modules = Pi::registry('modulelist')->read('active');
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

        return $nav;
    }

    /**
     * Navigation for block
     *
     * @param string $module
     * @return array
     */
    public static function block($module)
    {
        $pages = array();
        $nav = array(
            'pages'     => &$pages,
        );

        $model = Pi::model('block');
        $select = $model->select()->group('module')
            ->columns(array('count' => new Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $blockCounts = array();
        foreach ($rowset as $row) {
            $blockCounts[$row->module] = $row->count;
        }

        $modules = Pi::registry('modulelist')->read('active');
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

    /**
     * Navigation for pages
     *
     * @param string $module
     * @return array
     */
    public static function page($module)
    {
        $pages = array();
        $nav = array(
            'pages'     => &$pages,
        );

        $modules = Pi::registry('modulelist')->read('active');
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
