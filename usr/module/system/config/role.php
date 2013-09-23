<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * System role specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Admin section
    'admin' => array(
        // System administrator with ultra permissions
        'admin'     => __('Administrator'),
        // Admin area user
        'staff'     => __('Staff'),
        // Module/section moderator or administrator
        'moderator' => __('Moderator'),
        // Content editor
        'editor'    => __('Editor'),
        // Module manager for content and moderation
        'manager'   => __('Manager'),
    ),

    // Front section
    'front' => array(
        // System webmaster with ultra permissions
        'webmaster' => __('Webmaster'),
        // User
        'member'    => __('Member'),
        // Visitor
        'guest'     => __('Guest'),
    ),
);
