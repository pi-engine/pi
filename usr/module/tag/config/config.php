<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(

    'tag_delimiter' => array(
        'title'         => _t('Tag delimiter'),
        'description'   => _t('Delimiter(s) to separate tag terms, use `s` for space and separate delimiters with `|`.'),
        'value'         => '',
    ),

    'tag_quote' => array(
        'title'         => _t('Enable quote'),
        'description'   => _t('Use double quotes to identify multi-term tag.'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
    ),

    'min_length' => array(
        'title'         => _t('Minimum length'),
        'description'   => _t('Minimum length for valid tag terms.'),
        'value'         => 3,
        'filter'        => 'int',
    ),

    // Tag list item per page
    'item_per_page' => array(
        'title'         => _t('Item per page'),
        'description'   => _t('Number of items on tag list page.'),
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

    // Tag link item per page
    'detail_per_page' => array(
        'title'         => _t('Detail per page'),
        'description'   => _t('Number of items on tag detail page.'),
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

    // Link list item per page
    'link_per_page' => array(
        'title'         => _t('Link per page'),
        'description'   => _t('Number of items on one relationships page.'),
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
);
