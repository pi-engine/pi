<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // Front section
    'front' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => 'locale',
            'title'         => __('Module homepage'),
            'controller'    => 'index',
            'action'        => 'index',
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => 'locale',
            'title'         => __('Module'),
            'controller'    => 'index',
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('Module feeds'),
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('Test feeds'),
            'controller'    => 'index',
            'action'        => 'test',
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('Try feeds'),
            'controller'    => 'try',
        ),
    ),
);
