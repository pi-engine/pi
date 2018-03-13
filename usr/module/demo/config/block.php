<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Block with options and template
    'block-a' => [
        'title'       => _a('First Block'),
        'description' => _a('Block with options and tempalte'),
        'render'      => ['block', 'blocka'],
        'template'    => 'block-a',
        'config'      => [
            // text option
            'first'  => [
                'title'       => _a('Your input'),
                'description' => _a('The first option for first block'),
                'edit'        => 'text',
                'filter'      => 'string',
                'value'       => _a('Demo option 1'),
            ],
            // Yes or No option
            'second' => [
                'title'       => _a('Yes or No'),
                'description' => _a('Demo for Yes-No'),
                'edit'        => 'checkbox',
                'filter'      => 'int',
                'value'       => 0,
            ],
            // Number
            'third'  => [
                'title'       => _a('Input some figure'),
                'description' => _a('Demo for number'),
                'edit'        => 'text',
                //'filter'        => 'int',
                'value'       => 10,
            ],
        ],
        'access'      => [
            'guest'  => 1,
            'member' => 0,
        ],
    ],
    // Block with custom options and template
    'block-b' => [
        'title'       => _a('Second Block'),
        'description' => _a('Block with custom options and tempalte'),
        'render'      => ['block', 'blockb'],
        'template'    => 'block-b',
        'config'      => [
            // select option
            'first'  => [
                'title'       => _a('Select it'),
                'description' => '',
                'edit'        => [
                    'type'    => 'select',
                    'options' => [
                        'options' => [
                            'one'   => _a('One'),
                            'two'   => _a('Two'),
                            'three' => _a('Three'),
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => 'one',
            ],

            // Multi_checkbox
            'second' => [
                'title'       => _a('Check applicable'),
                'description' => '',
                'edit'        => [
                    'type'    => 'multi_checkbox',
                    'options' => [
                        'options' => [
                            'check_a' => _a('Check A'),
                            'check_b' => _a('Check B'),
                            'check_c' => _a('Check C'),
                        ],
                    ],
                ],
                'filter'      => 'array',
                'value'       => 'check_b',
            ],

            // module custom field option
            'third'  => [
                'title'       => _a('Choose it'),
                'description' => '',
                'edit'        => 'Module\Demo\Form\Element\Choose',
                'filter'      => 'string',
                'value'       => '',
            ],

        ],
    ],
    // Simple block w/o option, no template
    'block-c' => [
        'title'       => _a('Third Block'),
        'description' => _a('Block w/o options, no template'),
        'render'      => ['block', 'random'],
    ],
];
