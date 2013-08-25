<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        'resource'      => array(
            'resource'  => 'config',
        ),
    ),

    'block' => array(
        'label'         => _t('Blocks'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'block',
        'resource'      => array(
            'resource'  => 'block',
        ),
    ),

    'page' => array(
        'label'         => _t('Pages'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'page',
        'resource'      => array(
            'resource'  => 'page',
        ),
    ),

    'perm' => array(
        'label'         => _t('Resources'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'resource',
        'resource'      => array(
            'resource'  => 'resource',
        ),
    ),

    'event' => array(
        'label'         => _t('Event/Hook'),
        'route'         => 'admin',
        'module'        => 'system',
        'controller'    => 'event',
        'resource'      => array(
            'resource'  => 'event',
        ),
    ),
);
