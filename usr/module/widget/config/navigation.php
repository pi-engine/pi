<?php
/**
 * Widget module navigation config
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
 * @package         Module\Widget
 * @version         $Id$
 */

return array(
    'item'  => array(
        'front'     => false,
        'admin'     => array(
            'script'     => array(
                'label'         => 'Script widgets',
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'script',
                ),
            ),
            'static'     => array(
                'label'         => 'Static widgets',
                'route'         => 'admin',
                'controller'    => 'static',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'static',
                ),

                'pages'         => array(
                    'add'   => array(
                        'label'         => 'Add',
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'   => array(
                        'label'         => 'Edit',
                        'route'         => 'admin',
                        'controller'    => 'static',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'carousel'     => array(
                'label'         => 'Carousel widgets',
                'route'         => 'admin',
                'controller'    => 'carousel',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'carousel',
                ),

                'pages'         => array(
                    'add'   => array(
                        'label'         => 'Add',
                        'route'         => 'admin',
                        'controller'    => 'carousel',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'   => array(
                        'label'         => 'Edit',
                        'route'         => 'admin',
                        'controller'    => 'carousel',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
            'tab'     => array(
                'label'         => 'Compound tabs',
                'route'         => 'admin',
                'controller'    => 'tab',
                'action'        => 'index',
                'resource'      => array(
                    'resource'  => 'tab',
                ),

                'pages'         => array(
                    'add'   => array(
                        'label'         => 'Add',
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'add',
                        'visible'       => 0,
                    ),
                    'edit'   => array(
                        'label'         => 'Edit',
                        'route'         => 'admin',
                        'controller'    => 'tab',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                ),
            ),
        ),
    ),
);
