<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'front'    => array(
        // test
        'test'  => array(
            'title'         => _a('Test resource'),
            'access'    => array(
                'guest',
                'member',
            ),
        ),
        'write'  => array(
            'title'     => _a('Write privilege'),
            'access'    => 'member',
        ),
        'manage'  => array(
            'title'     => _a('Management privilege'),
            'access'    => 'moderator',
        ),
        'custom'    => 'Module\Demo\Api\PermFront',
    ),
    'admin'     => array(
        'custom'    => 'Module\Demo\Api\PermAdmin',
    ),
);
