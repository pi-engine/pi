<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // Block with options and template
    'block-a'   => array(
        'title'         => __('First Block'),
        'description'   => __('Block with options and tempalte'),
        'render'        => array('block', 'blocka'),
        'template'      => 'block-a',
        'config'        => array(
            // text option
            'first' => array(
                'title'         => 'Your input',
                'description'   => 'The first option for first block',
                'edit'          => 'text',
                'filter'        => 'string',
                'value'         => __('Demo option 1'),
            ),
            // Yes or No option
            'second'    => array(
                'title'         => 'Yes or No',
                'description'   => 'Demo for Yes-No',
                'edit'          => 'checkbox',
                'filter'        => 'number_int',
                'value'         => 0
            ),
            // Number
            'third'    => array(
                'title'         => 'Input some figure',
                'description'   => 'Demo for number',
                'edit'          => 'text',
                //'filter'        => 'number_int',
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
        'title'         => __('Second Block'),
        'description'   => __('Block with custom options and tempalte'),
        'render'        => array('block', 'blockb'),
        'template'      => 'block-b',
        'config'        => array(
            // select option
            'third' => array(
                'title'         => 'Select it',
                'description'   => '',
                'edit'          => array(
                    'type'          => 'select',
                    'options'    => array(
                        'options'   => array(
                            'one'   => 'One',
                            'two'   => 'Two',
                            'three' => 'Three',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => 'one',
            ),

            // module custom field option
            'fourth'    => array(
                'title'         => 'Choose it',
                'description'   => '',
                'edit'          => 'Module\Demo\Form\Element\Choose',
                'filter'        => 'string',
                'value'       => '',
            ),

        ),
    ),
    // Simple block w/o option, no template
    'block-c'   => array(
        'title'         => __('Third Block'),
        'description'   => __('Block w/o options, no tempalte'),
        'render'        => array('block', 'random'),
    ),
);
