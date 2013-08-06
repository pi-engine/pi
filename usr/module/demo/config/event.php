<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            // listener callback: class, method
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
