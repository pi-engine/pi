<?php
/**
 * System navigation config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Module\System
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
