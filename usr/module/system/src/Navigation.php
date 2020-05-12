<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System;

use Pi;
use Laminas\Db\Sql\Expression;

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
        $nav = [
            'parent' => [],
        ];

        $modules = Pi::registry('modulelist')->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $node = Pi::registry('navigation')->read($key . '-front');
            if (!is_array($node)) {
                continue;
            }

            $nav['parent'][$key] = [
                'label'  => $data['title'],
                'route'  => 'default',
                'module' => $key,
                'pages'  => $node,
            ];
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
        $pages = [];
        $nav   = [
            'parent' => &$pages,
        ];

        $modules = Pi::registry('modulelist')->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $pages[$key] = [
                'label'  => $data['title'],
                'module' => $key,
                'route'  => 'admin',
            ];
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
        $pages = [];
        $nav   = [
            'parent' => &$pages,
        ];

        $model        = Pi::model('config');
        $select       = $model->select()->group('module')
            ->columns(['count' => new Expression('count(*)'), 'module']);
        $rowset       = $model->selectWith($select);
        $configCounts = [];
        foreach ($rowset as $row) {
            $configCounts[$row->module] = $row->count;
        }

        $modules = Pi::registry('modulelist')->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            if (!empty($configCounts[$key])) {
                $pages[$key] = [
                    'label'      => $data['title'],
                    'module'     => $module,
                    'route'      => 'admin',
                    'controller' => 'config',
                    'action'     => 'module',
                    'params'     => [
                        'name' => $key,
                    ],
                ];
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
        $pages = [];
        $nav   = [
            'pages' => &$pages,
        ];

        $model       = Pi::model('block');
        $select      = $model->select()->group('module')
            ->columns(['count' => new Expression('count(*)'), 'module']);
        $rowset      = $model->selectWith($select);
        $blockCounts = [];
        foreach ($rowset as $row) {
            $blockCounts[$row->module] = $row->count;
        }

        $modules = Pi::registry('modulelist')->read('active');
        foreach ($modules as $key => $data) {
            if (empty($blockCounts[$key])) {
                continue;
            }
            $pages[$key] = [
                'label'      => $data['title'],
                'module'     => $module,
                'route'      => 'admin',
                'controller' => 'block',
                'params'     => [
                    'name' => $key,
                ],
                'pages'      => [
                    'edit'  => [
                        'label'      => __('Edit a block'),
                        'module'     => 'system',
                        'route'      => 'admin',
                        'controller' => 'block',
                        'action'     => 'edit',
                        'visible'    => 0,
                        'params'     => [
                            'name' => $key,
                        ],
                    ],
                    'clone' => [
                        'label'      => __('Clone a block'),
                        'module'     => 'system',
                        'route'      => 'admin',
                        'controller' => 'block',
                        'action'     => 'clone',
                        'visible'    => 0,
                        'params'     => [
                            'name' => $key,
                        ],
                    ],
                ],
            ];
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
        $pages = [];
        $nav   = [
            'pages' => &$pages,
        ];

        $modules      = Pi::registry('modulelist')->read('active');
        $systemModule = $modules['system'];
        unset($modules['system']);
        $modules = ['system' => $systemModule] + $modules;

        foreach ($modules as $key => $data) {
            $pages[$key] = [
                'label'      => $data['title'],
                'module'     => $module,
                'route'      => 'admin',
                'controller' => 'page',
                'params'     => [
                    'name' => $key,
                ],
                'pages'      => [
                    'layout' => [
                        'label'      => __('Block layout'),
                        'module'     => 'system',
                        'route'      => 'admin',
                        'controller' => 'page',
                        'action'     => 'block',
                        'visible'    => 0,
                        'params'     => [
                            'name' => $key,
                        ],
                    ],
                    'add'    => [
                        'label'      => __('Setup page'),
                        'module'     => 'system',
                        'route'      => 'admin',
                        'controller' => 'page',
                        'action'     => 'add',
                        'visible'    => 0,
                        'params'     => [
                            'name' => $key,
                        ],
                    ],
                ],
            ];
        }

        return $nav;
    }
}
