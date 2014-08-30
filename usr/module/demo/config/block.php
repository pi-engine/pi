<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Block with options and template
    'block-a'   => array(
        'title'         => _a('First Block'),
        'description'   => _a('Block with options and tempalte'),
        'render'        => array('block', 'blocka'),
        'template'      => 'block-a',
        'config'        => array(
            // text option
            'first' => array(
                'title'         => _a('Your input'),
                'description'   => _a('The first option for first block'),
                'edit'          => 'text',
                'filter'        => 'string',
                'value'         => _a('Demo option 1'),
            ),
            // Yes or No option
            'second'    => array(
                'title'         => _a('Yes or No'),
                'description'   => _a('Demo for Yes-No'),
                'edit'          => 'checkbox',
                'filter'        => 'int',
                'value'         => 0
            ),
            // Number
            'third'    => array(
                'title'         => _a('Input some figure'),
                'description'   => _a('Demo for number'),
                'edit'          => 'text',
                //'filter'        => 'int',
                'value'         => 10,
            ),
        ),
        'access'        => array(
            'guest'     => 1,
            'member'    => 0,
        ),
    ),
    // Block with custom options and template
    'block-b'   => array(
        'title'         => _a('Second Block'),
        'description'   => _a('Block with custom options and tempalte'),
        'render'        => array('block', 'blockb'),
        'template'      => 'block-b',
        'config'        => array(
            // select option
            'first' => array(
                'title'         => _a('Select it'),
                'description'   => '',
                'edit'          => array(
                    'type'          => 'select',
                    'options'    => array(
                        'options'   => array(
                            'one'   => _a('One'),
                            'two'   => _a('Two'),
                            'three' => _a('Three'),
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => 'one',
            ),

            // Multi_checkbox
            'second' => array(
                'title'         => _a('Check applicable'),
                'description'   => '',
                'edit'          => array(
                    'type'          => 'multi_checkbox',
                    'options'    => array(
                        'options'   => array(
                            'check_a' => _a('Check A'),
                            'check_b' => _a('Check B'),
                            'check_c' => _a('Check C'),
                        ),
                    ),
                ),
                'filter'        => 'array',
                'value'         => 'check_b',
            ),

            // module custom field option
            'third'    => array(
                'title'         => _a('Choose it'),
                'description'   => '',
                'edit'          => 'Module\Demo\Form\Element\Choose',
                'filter'        => 'string',
                'value'       => '',
            ),

        ),
    ),
    // Simple block w/o option, no template
    'block-c'   => array(
        'title'         => _a('Third Block'),
        'description'   => _a('Block w/o options, no template'),
        'render'        => array('block', 'random'),
    ),
);
