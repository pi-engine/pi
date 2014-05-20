<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Event/listener specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Event list
    'event'    => array(
        // event name (unique)
        'module_install' => array(
            // title
            'title' => _t('Module installed'),
        ),
        'module_uninstall'  => array(
            'title' => _t('Module uninstalled'),
        ),
        'module_activate'  => array(
            'title' => _t('Module activated'),
        ),
        'module_deactivate'  => array(
            'title' => _t('Module deactivated'),
        ),
        'module_update'  => array(
            'title' => _t('Module updated'),
        ),
    ),
    // Listener list
    'listener' => array(
        array(
            // event info: module, event name
            'event'     => array('system', 'module_install'),
            // listener callback: class, method
            'callback'  => array('event', 'moduleinstall'),
        ),
        array(
            'event'     => array('system', 'module_uninstall'),
            'callback'  => array('event', 'moduleuninstall'),
        ),
        array(
            'event'     => array('system', 'module_update'),
            'callback'  => array('event', 'moduleupdate'),
        ),
        array(
            'event'     => array('system', 'module_activate'),
            'callback'  => array('event', 'moduleactivate'),
        ),
        array(
            'event'     => array('system', 'module_deactivate'),
            'callback'  => array('event', 'moduledeactivate'),
        ),
    ),
);
