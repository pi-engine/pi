<?php
/**
 * System module navigation config
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

// System settings, don't change
return array(
    'meta' => array(
        // Front-end navigation template
        'front'     => array(
            'name'      => 'front',
            'section'   => 'front',
            'title'    => 'Front navigation',
        ),
        // Back-end navigation template
        'admin'     => array(
            'name'      => 'admin',
            'section'   => 'admin',
            'title'     => 'Admin navigation',
        ),
        // Managed components
        'component' => array(
            'name'      => 'component',
            'section'   => 'admin',
            'title'     => 'Managed components',
        ),
    ),
    'item' => array(
        // Front navigation items
        'front' => include __DIR__ . '/navigation.front.php',
        // Admin navigation items
        'admin' => include __DIR__ . '/navigation.admin.php',
        // Managed component items
        'component' => include __DIR__ . '/navigation.component.php',
    )
);
