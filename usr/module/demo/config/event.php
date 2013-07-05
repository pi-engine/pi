<?php
/**
 * Demo module event config
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
 * @package         Module\Demo
 * @version         $Id$
 */

return array(
    // Event list
    'events'    => array(
        // event name (unique)
        'user_call' => array(
            // title
            'title' => __('Event hook demo'),
        ),
    ),
    // Listener list
    'listeners' => array(
        array(
            // event info: module, event name
            'event'     => array('pm', 'test'),
            // listener info: class, method
            'listener'  => array('event', 'message'),
        ),
        array(
            'event'     => array('demo', 'user_call'),
            'listener'  => array('event', 'selfcall'),
        ),
        array(
            'event'     => array('system', 'module_install'),
            'listener'  => array('event', 'moduleinstall'),
        ),
        array(
            'event'     => array('system', 'module_update'),
            'listener'  => array('event', 'moduleupdate'),
        ),
    ),
);
