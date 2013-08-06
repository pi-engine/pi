<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'title'         => __('Navigation'),
    'description'   => __('Block to display navigation menu and breadcrumbs'),
    'template'      => 'navigation.phtml',
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
