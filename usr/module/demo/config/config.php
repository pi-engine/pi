<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Categories for config edit or display
    'category' => [
        [
            'title' => _t('General'),
            'name'  => 'general',
        ],
        [
            'title' => _t('Test'),
            'name'  => 'test',
        ],
    ],
    // Config items
    'item'     => [
        'item_per_page' => [
            'category'    => 'general',
            'title'       => _t('Item per page'),
            'description' => _t('Number of items on one page.'),
            'value'       => 10,
            'filter'      => 'int',
            'edit'        => [
                'type'    => 'select',
                'options' => [
                    'options' => [
                        10 => '10',
                        20 => '20',
                        50 => '50',
                    ],
                ],
            ],
        ],

        'test' => [
            'category'    => 'test',
            'title'       => _t('Test Config'),
            'description' => _t('An example for configuration.'),
            'value'       => 'Configuration text for testing',
        ],

        'add_select_one' => [
            'category'    => 'general',
            'title'       => _t('Add Item'),
            'description' => _t('Example for single select.'),
            'edit'        => [
                'type'    => 'select',
                'options' => [
                    'options' => [
                        1 => _t('One'),
                        2 => _t('Two'),
                        3 => _t('Three'),
                    ],
                ],
            ],
            'value'       => 2,
        ],

        'add_select_multiple' => [
            'category'    => 'general',
            'title'       => _t('Add Item'),
            'description' => _t('Example for multiple select.'),
            'edit'        => [
                'type'       => 'select',
                'attributes' => [
                    'multiple' => true,
                ],
                'options'    => [
                    'options' => [
                        1 => _t('One'),
                        2 => _t('Two'),
                        3 => _t('Three'),
                    ],
                ],
            ],
            'filter'      => 'array',
            'value'       => [1, 2],
        ],

        'add_checkbox' => [
            'category'    => 'general',
            'title'       => _t('Add Item'),
            'description' => _t('Example for checkbox.'),
            'edit'        => [
                'type'    => 'checkbox',
                'options' => [
                    'options' => [
                        1 => _t('One'),
                        2 => _t('Two'),
                        3 => _t('Three'),
                    ],
                ],
            ],
            'value'       => 2,
        ],

        'add_multi_checkbox' => [
            'category'    => 'general',
            'title'       => _t('Add Item'),
            'description' => _t('Example for multiple checkbox.'),
            'edit'        => [
                'type'    => 'multi_checkbox',
                'options' => [
                    'options' => [
                        1 => _t('One'),
                        2 => _t('Two'),
                        3 => _t('Three'),
                    ],
                ],
            ],
            'filter'      => 'array',
            'value'       => [1, 2],
        ],

        'add_radio' => [
            'category'    => 'general',
            'title'       => _t('Add Item'),
            'description' => _t('Example for radio.'),
            'edit'        => [
                'type'    => 'radio',
                'options' => [
                    'options' => [
                        1 => _t('One'),
                        2 => _t('Two'),
                        3 => _t('Three'),
                    ],
                ],
            ],
            'value'       => 2,
        ],
    ],
];
