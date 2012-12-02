<?php
/**
 * Demo module block specs
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
 * @package         Module\Demo
 * @version         $Id$
 */

// Block list
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

            // module custom field option, defined in app/demo/class/form/element/choose.php
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
