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
        'register_user' => array(
            // title
            'title' => __('Register user'),
        ),

        // Activate user event
        'activate_user' => array(
            // title
            'title' => __('Active user'),
        ),

        // Update email event
        'update_email' => array(
            // title
            'title' => __('Update email'),
        ),

        // Update display name event
        'update_name' => array(
            // title
            'title' => __('Update display name'),
        ),

        // Update password event
        'update_password' => array(
            // title
            'title' => __('Update password'),
        ),

        // Update avatar event
        'update_avatar' => array(
            // title
            'title' => __('Update avatar'),
        ),

        // Enable user
        'enable_user' => array(
            // title
            'title' => __('Enable user'),
        ),

        // Disable user
        'disable_user' => array(
            // title
            'title' => __('Disable user'),
        ),

        // Delete user
        'delete_user' => array(
            // title
            'title' => __('Delete user'),
        ),

        // Assign role
        'assign_role' => array(
            // title
            'title' => __('Assign role'),
        ),

        // Remove role
        'remove_role' => array(
            // title
            'title' => __('Remove role'),
        ),

        // Login
        'login' => array(
            // title
            'title' => __('Login'),
        ),

        // Logout
        'logout' => array(
            // title
            'title' => __('Logout'),
        ),
    ),
    // Listener list
    'listener' => array(
        array(
            // event info: module, event name
            'event'     => array('user', 'activate_user'),
            // listener callback: class, method
            'callback'  => array('event', 'joincommunity'),
        ),
    ),
);
