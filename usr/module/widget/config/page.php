<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Admin section
    'admin' => array(
        array(
            'controller'    => 'script',
            'permission'    => 'script',
        ),
        array(
            'controller'    => 'static',
            'permission'    => 'static',
        ),
        array(
            'controller'    => 'list',
            'permission'    => 'list',
        ),
        array(
            'controller'    => 'media',
            'permission'    => 'media',
        ),
        array(
            'controller'    => 'carousel',
            'permission'    => 'carousel',
        ),
        array(
            'controller'    => 'spotlight',
            'permission'    => 'spotlight',
        ),
        array(
            'controller'    => 'tab',
            'permission'    => 'tab',
        ),
    ),
);
