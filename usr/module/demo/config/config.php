<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return array(
    // Categories for config edit or display
    'category'  => array(
        array(
            'title' => _t('General'),
            'name'  => 'general',
        ),
        array(
            'title' => _t('Test'),
            'name'  => 'test'
        ),
    ),
    // Config items
    'item'         => array(
        'item_per_page' => array(
            'category'      => 'general',
            'title'         => _t('Item per page'),
            'description'   => _t('Number of items on one page.'),
            'value'         => 10,
            'filter'        => 'int',
            'edit'          => array(
                'type'      => 'select',
                'options'   => array(
                    'options'   => array(
                        10  => '10',
                        20  => '20',
                        50  => '50',
                    ),
                ),
            ),
        ),

        'test'  => array(
            'category'      => 'test',
            'title'         => _t('Test Config'),
            'description'   => _t('An example for configuration.'),
            'value'         => 'Configuration text for testing'
        ),

        'add_select_one'   => array(
            'category'      => 'general',
            'title'         => _t('Add Item'),
            'description'   => _t('Example for single select.'),
            'edit'          => array(
                'type'      => 'select',
                'options'   => array(
                    'options'   => array(
                        1   => _t('One'),
                        2   => _t('Two'),
                        3   => _t('Three'),
                    ),
                ),
            ),
            'value'         => 2,
        ),

        'add_select_multiple'   => array(
            'category'      => 'general',
            'title'         => _t('Add Item'),
            'description'   => _t('Example for multiple select.'),
            'edit'          => array(
                'type'      => 'select',
                'attributes'    => array(
                    'multiple'  => true,
                ),
                'options'   => array(
                    'options'   => array(
                        1   => _t('One'),
                        2   => _t('Two'),
                        3   => _t('Three'),
                    ),
                ),
            ),
            'filter'        => 'array',
            'value'         => array(1, 2),
        ),

        'add_checkbox'   => array(
            'category'      => 'general',
            'title'         => _t('Add Item'),
            'description'   => _t('Example for checkbox.'),
            'edit'          => array(
                'type'      => 'checkbox',
                'options'   => array(
                    'options'   => array(
                        1   => _t('One'),
                        2   => _t('Two'),
                        3   => _t('Three'),
                    ),
                ),
            ),
            'value'         => 2,
        ),

        'add_multi_checkbox'   => array(
            'category'      => 'general',
            'title'         => _t('Add Item'),
            'description'   => _t('Example for multiple checkbox.'),
            'edit'          => array(
                'type'      => 'multi_checkbox',
                'options'   => array(
                    'options'   => array(
                        1   => _t('One'),
                        2   => _t('Two'),
                        3   => _t('Three'),
                    ),
                ),
            ),
            'filter'        => 'array',
            'value'         => array(1, 2),
        ),

        'add_radio'   => array(
            'category'      => 'general',
            'title'         => _t('Add Item'),
            'description'   => _t('Example for radio.'),
            'edit'          => array(
                'type'      => 'radio',
                'options'   => array(
                    'options'   => array(
                        1   => _t('One'),
                        2   => _t('Two'),
                        3   => _t('Three'),
                    ),
                ),
            ),
            'value'         => 2,
        ),
    ),
);
