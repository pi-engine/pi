<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'title'       => _a('Navigation'),
    'description' => _a('Block to display navigation menu and breadcrumbs'),
    'template'    => 'navigation.phtml',
    'config'      => [
        'navigation'           => [
            'title' => _a('Navigation name'),
            'edit'  => 'navigation',
            'value' => '',
        ],
        'menu_show'            => [
            'title' => _a('Display menu'),
            'edit'  => 'checkbox',
            //'filter'        => 'int',
            'value' => '1',
        ],
        'menu_ul_class'        => [
            'title'       => _a('ul class for menu'),
            'description' => _a('Separate classes with space'),
            'edit'        => 'text',
            'filter'      => 'string',
            'value'       => 'nav nav-tabs',
        ],
        'menu_min_depth'       => [
            'title'  => _a('Minimum depth for menu'),
            'edit'   => 'text',
            'filter' => 'int',
            'value'  => 1,
        ],
        'menu_max_depth'       => [
            'title'       => _a('Maximum depth for menu'),
            'description' => '0 for no limit',
            'edit'        => 'text',
            'filter'      => 'int',
            'value'       => 0,
        ],
        'breadcrumb_show'      => [
            'title' => _a('Display menu'),
            'edit'  => 'checkbox',
            //'filter'        => 'int',
            'value' => '0',
        ],
        'breadcrumb_separator' => [
            'title'  => _a('Separator for breadcrumb'),
            'edit'   => 'text',
            'filter' => 'string',
            'value'  => '&gt;',
        ],
        'breadcrumb_min_depth' => [
            'title'  => _a('Minimum depth for breadcrumb'),
            'edit'   => 'text',
            'filter' => 'int',
            'value'  => 1,
        ],
        'breadcrumb_link_last' => [
            'title' => _a('Add link to last page'),
            'edit'  => 'checkbox',
            //'filter'        => 'int',
            'value' => '0',
        ],
    ],
];
