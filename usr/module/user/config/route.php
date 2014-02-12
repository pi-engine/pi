<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Route specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // System user route
    'user'  => array(
        'name'      => 'user',
        'type'      => 'Module\User\Route\User',
        'priority'  => 5,
        'options'   => array(
            'route'    => '/user',
        ),
    ),
);
