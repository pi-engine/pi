<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return array(
    'item'  => array(
        'social_sharing'    => array(
            'title'         => _t('Social sharing items'),
            'description'   => '',
            'edit'          => array(
                'type'      => 'multi_checkbox',
                'options'   => array(
                    'options'   => Pi::service('social_sharing')->getList(),
                ),
            ),
            'filter'        => 'array',
        ),
        'show_breadcrumbs' => array(
            'title'        => _a('Show breadcrumbs'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
    ),
);