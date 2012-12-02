<?php
/**
 * Widget for display a navigation menu/breadcrumb
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

// Widget meta
return array(
    'title'         => __('Navigation'),
    'description'   => __('Block to display navigation menu and breadcrumbs'),
    'config'        => array(
        'navigation'    => array(
            'title'         => 'Navigation name',
            'edit'          => 'navigation',
            //'filter'        => 'number_int',
            'value'         => '',
        ),
        'menu_show'    => array(
            'title'         => 'Display menu',
            'edit'          => 'checkbox',
            //'filter'        => 'number_int',
            'value'         => '1',
        ),
        'menu_ul_class' => array(
            'title'         => 'ul class for menu',
            'description'   => 'Separate classes with space',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => 'nav nav-tabs',
        ),
        'menu_min_depth' => array(
            'title'         => 'Minimum depth for menu',
            'edit'          => 'text',
            'filter'        => 'number_int',
            'value'         => 1,
        ),
        'menu_max_depth' => array(
            'title'         => 'Maximum depth for menu',
            'description'   => '0 for no limit',
            'edit'          => 'text',
            'filter'        => 'number_int',
            'value'         => 0,
        ),
        'breadcrumb_show'    => array(
            'title'         => 'Display menu',
            'edit'          => 'checkbox',
            //'filter'        => 'number_int',
            'value'         => '0',
        ),
        'breadcrumb_separator' => array(
            'title'         => 'Separator for breadcrumb',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => '&gt;',
        ),
        'breadcrumb_min_depth' => array(
            'title'         => 'Minimum depth for breadcrumb',
            'edit'          => 'text',
            'filter'        => 'number_int',
            'value'         => 1,
        ),
        'breadcrumb_link_last'    => array(
            'title'         => 'Add link to last page',
            'edit'          => 'checkbox',
            //'filter'        => 'number_int',
            'value'         => '0',
        ),
    ),
);