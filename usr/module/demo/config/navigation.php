<?php
/**
 * Demo module navigation config
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
 * @package         Module\Demo
 * @version         $Id$
 */

return array(
    //'translate' => 'navigation',
    'front'   => array(
        'tree'     => array(
            'label'         => 'Test User Call',
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'user',
        ),
        'pagea'     => array(
            'label'         => 'Homepage',
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'index',

            'pages'         => array(
                'paginator' => array(
                    'label'         => 'Full Paginator',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'page',
                ),
                'simple'    => array(
                    'label'         => 'Lean Paginator',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'simple',
                ),
                'pageaa'    => array(
                    'label'         => 'Subpage one',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'index',
                ),
                'pageab'    => array(
                    'label'         => 'Subpage two',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'index',
                    'params'        => array(
                        'op'    => 'test',
                    ),

                    'pages'         => array(
                        'pageaba'   => array(
                            'label'         => 'Leaf one',
                            'route'         => 'default',
                            'controller'    => 'index',
                            'action'        => 'index',
                            'params'        => array(
                                'op'    => 'test',
                                'page'  => 2,
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'route' => array(
            'label'         => 'Routes',
            'route'         => 'default',
            'controller'    => 'route'
        ),
    ),
    'admin' => array(
        'pagea'     => array(
            'label'         => 'Sample',
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'route'     => array(
            'label'         => 'Routes',
            'route'         => 'admin',
            'controller'    => 'route',
            'action'        => 'index',
        ),
    ),
);
