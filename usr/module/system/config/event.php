<?php
/**
 * System module event config
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
    // Event list
    'events'    => array(
        // event name (unique)
        'module_install' => array(
            // title
            'title' => __('Module installation'),
        ),
        'module_uninstall'  => array(
            'title' => __('Module uninstallation'),
        ),
        'module_activate'  => array(
            'title' => __('Module activation'),
        ),
        'module_deactivate'  => array(
            'title' => __('Module deactivation'),
        ),
        'module_update'  => array(
            'title' => __('Module update'),
        ),
    ),
    // Listener list
    'listeners' => array(
        array(
            // event info: module, event name
            'event'     => array('system', 'module_install'),
            // listener info: class, method
            'listener'  => array('event', 'moduleinstall'),
        ),
    ),
);
