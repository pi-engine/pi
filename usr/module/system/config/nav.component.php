<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * System component navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'config'        => array(
        'label'         => _t('Configurations'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'config',

        'permission'    => array(
            'resource'  => 'config',
        ),
    ),

    'block' => array(
        'label'         => _t('Blocks'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'block',

        'permission'    => array(
            'resource'  => 'block',
        ),
    ),

    'page' => array(
        'label'         => _t('Pages'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'page',

        'permission'    => array(
            'resource'  => 'page',
        ),
    ),

    'cache' => array(
        'label'         => _t('Cache'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'cache',

        'permission'    => array(
            'resource'  => 'cache',
        ),
    ),

    'perm' => array(
        'label'         => _t('Permission'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'perm',

        'permission'    => array(
            'resource'  => 'permission',
        ),
    ),

    'event' => array(
        'label'         => _t('Event/Hook'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'event',

        'permission'    => array(
            'resource'  => 'event',
        ),
    ),
);
