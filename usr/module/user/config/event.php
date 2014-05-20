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
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
return array(
    // Event list
    'event'    => array(
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

        // Update display name event
        'name_change' => array(
            // title
            'title' => __('Display name changed'),
        ),

        // Update email event
        'email_change' => array(
            // title
            'title' => __('Email changed'),
        ),

        // Update avatar event
        'avatar_change' => array(
            // title
            'title' => __('Avatar changed'),
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
    ),

    // Listener list
    'listener' => array(
        array(
            'event'     => array('user', 'user_register'),
            'callback'  => array('event', 'userRegister'),
        ),
        array(
            'event'     => array('user', 'user_activate'),
            'callback'  => array('event', 'userActivate'),
        ),
        array(
            'event'     => array('user', 'user_enable'),
            'callback'  => array('event', 'userEnable'),
        ),
        array(
            'event'     => array('user', 'user_disable'),
            'callback'  => array('event', 'userDisable'),
        ),
        array(
            'event'     => array('user', 'user_delete'),
            'callback'  => array('event', 'userDelete'),
        ),
        array(
            'event'     => array('user', 'user_update'),
            'callback'  => array('event', 'userUpdate'),
        ),
        array(
            'event'     => array('user', 'name_change'),
            'callback'  => array('event', 'nameChange'),
        ),
        array(
            'event'     => array('user', 'email_change'),
            'callback'  => array('event', 'emailChange'),
        ),
        array(
            'event'     => array('user', 'avatar_change'),
            'callback'  => array('event', 'avatarChange'),
        ),
        array(
            'event'     => array('user', 'password_change'),
            'callback'  => array('event', 'passwordChange'),
        ),
        array(
            'event'     => array('user', 'role_assign'),
            'callback'  => array('event', 'roleAssign'),
        ),
        array(
            'event'     => array('user', 'role_remove'),
            'callback'  => array('event', 'roleRemove'),
        ),
        array(
            'event'     => array('user', 'user_login'),
            'callback'  => array('event', 'userLogin'),
        ),
        array(
            'event'     => array('user', 'user_logout'),
            'callback'  => array('event', 'userLogout'),
        ),
    ),
);
