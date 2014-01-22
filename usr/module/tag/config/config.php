<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'min_length' => array(
        'title'         => _t('Minimum length'),
        'description'   => _t('Minimum length for valid tag terms.'),
        'value'         => 3,
        'filter'        => 'int',
    ),

    // Tag list item per page
    'item_per_page' => array(
        'category'      => 'general',
        'title'         => _t('Item per page'),
        'description'   => _t('Number of items on tag list page.'),
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

    // Tag link item per page
    'detail_per_page' => array(
        'category'      => 'general',
        'title'         => _t('Detail per page'),
        'description'   => _t('Number of items on tag detail page.'),
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

    // Link list item per page
    'link_per_page' => array(
        'category'      => 'general',
        'title'         => _t('Link per page'),
        'description'   => _t('Number of items on one relationships page.'),
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
);
