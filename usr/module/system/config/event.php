<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Event/listener specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
            // listener callback: class, method
            'listener'  => array('event', 'moduleinstall'),
        ),
    ),
);
