<?php
/**
 * System module page config
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
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

return array(
    // Front section
    'front' => array(
        // homepage
        array(
            'title'         => __('Homepage'),
            'module'        => 'system',
            'controller'    => 'index',
            'action'        => 'index',
            'block'         => 1,
        ),
        // utility page, not used yet
        array(
            'title'         => __('Utility'),
            'module'        => 'system',
            'controller'    => 'utility',
            'block'         => 1,
        ),
        // error message page
        array(
            'title'         => __('Error reporting'),
            'module'        => 'system',
            'controller'    => 'error',
            'block'         => 0,
        ),
    ),
    // Admin section
    'admin' => array(
        // System admin generic access
        array(
            'controller'    => 'index',
            'permission'    => array(
                'parent'        => 'admin',
            ),
        ),
        // System readme
        array(
            'controller'    => 'readme',
            'permission'    => array(
                'parent'        => 'admin',
            ),
        ),

        // System specs
        // preferences
        array(
            'controller'    => 'preference',
            'permission'    => array(
                'parent'        => 'preference',
            ),
        ),
        // appearance
        array(
            'controller'    => 'block',
            'permission'    => array(
                'parent'        => 'appearance',
            ),
        ),
        array(
            'controller'    => 'theme',
            'permission'    => array(
                'parent'        => 'appearance',
            ),
        ),
        // permissions
        array(
            'controller'    => 'role',
            'permission'    => array(
                'parent'        => 'permission',
            ),
        ),
        array(
            'controller'    => 'resource',
            'permission'    => array(
                'parent'        => 'permission',
            ),
        ),
        array(
            'parent'        => 'permission',
            'controller'    => 'rule',
            'permission'    => array(
                'parent'        => 'admin',
            ),
        ),
        // modules
        array(
            'controller'    => 'module',
            'permission'    => array(
                'parent'        => 'module',
            ),
        ),
        // maintenance
        array(
            'controller'    => 'asset',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
        array(
            'controller'    => 'audit',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
        array(
            'controller'    => 'cache',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
        array(
            'controller'    => 'event',
            'permission'    => array(
                'parent'        => 'maintenance',
            ),
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_expire'  => 0,
            'cache_level'   => '',
            'title'         => __('What\'s new'),
        ),
    ),
);
