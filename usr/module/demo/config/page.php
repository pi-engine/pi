<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Front section
    'front' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => 'locale',
            'title'         => _a('Module homepage'),
            'controller'    => 'index',
            'action'        => 'index',
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => 'locale',
            'title'         => _a('Module'),
            'controller'    => 'index',
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => _a('Module feeds'),
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => _a('Test feeds'),
            'controller'    => 'index',
            'action'        => 'test',
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => _a('Try feeds'),
            'controller'    => 'try',
        ),
    ),
);
