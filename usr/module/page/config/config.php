<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'category' => [
        [
            'title' => _a('Admin'),
            'name'  => 'admin',
        ],
        [
            'title' => _a('Image'),
            'name'  => 'image',
        ],
        [
            'title' => _a('Social'),
            'name'  => 'social',
        ],
    ],
    'item'     => [
        'show_breadcrumbs' => [
            'title'       => _a('Show breadcrumbs'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1,
            'category'    => 'admin',
        ],
        'bypass_uri'       => [
            'title'       => _a('Bypass URI list'),
            'description' => _a(
                'Usefull in case you use module renaming, to permit the page module to skip the /page/ module name prefix in url path.<br>Put one relative url per line. The url must start with a dash /.<br>Example : for Portfolio module, renamed to partners, you have to put /partners'
            ),
            'edit'        => 'textarea',
            'filter'      => 'string',
            'value'       => '',
            'category'    => 'admin',
        ],
        'social_sharing'   => [
            'title'       => _t('Social sharing items'),
            'description' => '',
            'edit'        => [
                'type'    => 'multi_checkbox',
                'options' => [
                    'options' => Pi::service('social_sharing')->getList(),
                ],
            ],
            'filter'      => 'array',
            'category'    => 'social',
        ],
        'main_image_height'             => [
            'category'    => 'image',
            'title'       => _a('Main Image resize height'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 1200,
        ],
        'main_image_width'             => [
            'category'    => 'image',
            'title'       => _a('Main Image resize width'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 250,
        ],
    ],
];
