<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Routes
 */

return array(
    // route name
    'comment'  => array(
        'name'      => 'comment',
        'type'      => 'Module\Comment\Route\Comment',
        'options'   => array(
            'prefix'    => '/comment',
            'defaults'  => array(
                'module'        => 'comment',
                'controller'    => 'index',
                'action'        => 'index'
            )
        ),
    )
);

