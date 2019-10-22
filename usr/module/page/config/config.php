<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'item' => [
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
        ],
        'show_breadcrumbs' => [
            'title'       => _a('Show breadcrumbs'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1,
        ],
        'bypass_uri' => [
            'title'       => _a('Bypass URI list'),
            'description' => 'Usefull in case you use module renaming, to permit the page module to skip the /page/ module name prefix in url path.<br>Put one relative url per line. The url must start with a dash /.<br>Example : for Portfolio module, renamed to partners, you have to put /partners',
            'edit'        => 'textarea',
            'filter'      => 'string',
            'value'       => '',
        ],
    ],
];