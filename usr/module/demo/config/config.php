<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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

        'add'   => array(
            'category'      => 'general',
            'title'         => _t('Add Item'),
            'description'   => _t('An example for adding configuration.'),
            'edit'          => array(
                'type'      => 'select',
                'attributes'    => array(
                    'multiple'  => true,
                ),
                'options'   => array(
                    'options'   => array(
                        1   => 'One',
                        2   => 'Two',
                        3   => 'Three',
                    ),
                ),
            ),
            'filter'        => 'array',
            'value'         => array(1, 2),
        )
    )
);
