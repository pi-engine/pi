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
        // Register user event
        'user_register' => array(
            // title
            'title' => __('User registered'),
        ),

        // Activate user event
        'user_activate' => array(
            // title
            'title' => __('User activated'),
        ),

        // Enable user
        'user_enable' => array(
            // title
            'title' => __('User enabled'),
        ),

        // Disable user
        'user_disable' => array(
            // title
            'title' => __('User disabled'),
        ),

        // Delete user
        'user_delete' => array(
            // title
            'title' => __('User deleted'),
        ),

        // Update display name event
        'user_update' => array(
            // title
            'title' => __('User profile updated'),
        ),

        // Update email event
        'email_change' => array(
            // title
            'title' => __('Email changed'),
        ),

        // Update password event
        'password_change' => array(
            // title
            'title' => __('Password changed'),
        ),

        // Assign role
        'role_assign' => array(
            // title
            'title' => __('Role assigned'),
        ),

        // Remove role
        'role_remove' => array(
            // title
            'title' => __('Role removed'),
        ),

        // Login
        'user_login' => array(
            // title
            'title' => __('User logged in'),
        ),

        // Logout
        'user_logout' => array(
            // title
            'title' => __('User logged out'),
        ),
    ),

    // Listener list
    'listener' => array(
        array(
            // event info: module, event name
            'event'     => array('user', 'user_activate'),
            // listener callback: class, method
            'callback'  => array('event', 'joincommunity'),
        ),
    ),
);
