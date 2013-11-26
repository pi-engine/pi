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
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
return array(
    // Event list
    'event'    => array(
        // event name (unique)
        'join_community' => array(
            // title
            'title' => __('Join community'),
        ),
    ),
    // Listener list
    'listener' => array(
        array(
            // event info: module, event name
            'event'     => array('user', 'join_community'),
            // listener callback: class, method
            'callback'  => array('event', 'joincommunity'),
        ),
    ),
);
