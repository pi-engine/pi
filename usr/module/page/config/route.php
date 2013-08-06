<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // route name
    'page'  => array(
        'section'   => 'front',
        'priority'  => 10,

        'type'      => 'Module\Page\Route\Page',
        'options'   => array(
            'route'     => '/page',
            'defaults'  => array(
                'module'        => 'page',
                'controller'    => 'index',
                'action'        => 'index'
            )
        ),
    )
);
