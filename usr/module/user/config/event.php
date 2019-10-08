<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Event/listener specs
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
return [
    // Event list
    'event'    => [
        // Login
        'user_login'      => [
            // title
            'title' => __('User logged in'),
        ],

        // Logout
        'user_logout'     => [
            // title
            'title' => __('User logged out'),
        ],

        // Register user event
        'user_register'   => [
            // title
            'title' => __('User registered'),
        ],

        // Activate user event
        'user_activate'   => [
            // title
            'title' => __('User activated'),
        ],

        // Enable user
        'user_enable'     => [
            // title
            'title' => __('User enabled'),
        ],

        // Disable user
        'user_disable'    => [
            // title
            'title' => __('User disabled'),
        ],

        // Delete user
        'user_delete'     => [
            // title
            'title' => __('User deleted'),
        ],

        // Update display name event
        'user_update'     => [
            // title
            'title' => __('User profile updated'),
        ],

        // Update display name event
        'name_change'     => [
            // title
            'title' => __('Display name changed'),
        ],

        // Update email event
        'email_change'    => [
            // title
            'title' => __('Email changed'),
        ],

        // Update avatar event
        'avatar_change'   => [
            // title
            'title' => __('Avatar changed'),
        ],

        // Update password event
        'password_change' => [
            // title
            'title' => __('Password changed'),
        ],

        // Assign role
        'role_assign'     => [
            // title
            'title' => __('Role assigned'),
        ],

        // Remove role
        'role_remove'     => [
            // title
            'title' => __('Role removed'),
        ],
    ],

    // Listener list
    'listener' => [
        [
            'event'    => ['user', 'user_register'],
            'callback' => ['event', 'userRegister'],
        ],
        [
            'event'    => ['user', 'user_activate'],
            'callback' => ['event', 'userActivate'],
        ],
        [
            'event'    => ['user', 'user_enable'],
            'callback' => ['event', 'userEnable'],
        ],
        [
            'event'    => ['user', 'user_disable'],
            'callback' => ['event', 'userDisable'],
        ],
        [
            'event'    => ['user', 'user_delete'],
            'callback' => ['event', 'userDelete'],
        ],
        [
            'event'    => ['user', 'user_update'],
            'callback' => ['event', 'userUpdate'],
        ],
        [
            'event'    => ['user', 'name_change'],
            'callback' => ['event', 'nameChange'],
        ],
        [
            'event'    => ['user', 'email_change'],
            'callback' => ['event', 'emailChange'],
        ],
        [
            'event'    => ['user', 'avatar_change'],
            'callback' => ['event', 'avatarChange'],
        ],
        [
            'event'    => ['user', 'password_change'],
            'callback' => ['event', 'passwordChange'],
        ],
        [
            'event'    => ['user', 'role_assign'],
            'callback' => ['event', 'roleAssign'],
        ],
        [
            'event'    => ['user', 'role_remove'],
            'callback' => ['event', 'roleRemove'],
        ],
        [
            'event'    => ['user', 'user_login'],
            'callback' => ['event', 'userLogin'],
        ],
        [
            'event'    => ['user', 'user_logout'],
            'callback' => ['event', 'userLogout'],
        ],
    ],
];
