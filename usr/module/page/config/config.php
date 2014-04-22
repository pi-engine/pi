<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'category' => array(
        array(
            'title' => __('Social networking'),
            'name' => 'social'
        ),
    ),
    'item' => array(
        // Social
        'social_gplus' => array(
            'category'      => 'social',
            'title'         => _t('Enable Google Plus'),
            'description'   => '',
            'edit'          => 'checkbox',
            'filter'        => 'int',
            'value'         => 0
        ),
        'social_facebook' => array(
            'category'      => 'social',
            'title'         => _t('Enable Facebook'),
            'description'   => '',
            'edit'          => 'checkbox',
            'filter'        => 'int',
            'value'         => 0
        ),
        'social_twitter' => array(
            'category'      => 'social',
            'title'         => __('Enable Twitter'),
            'description'   => '',
            'edit'          => 'checkbox',
            'filter'        => 'int',
            'value'         => 0
        ),
    ),
);