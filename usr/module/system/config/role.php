<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        'admin'     => _a('Administrator'),
        // Admin area user
        'staff'     => _a('Staff'),
        /*
        // Module/section moderator or administrator
        'moderator' => _a('Moderator'),
        // Content editor
        'editor'    => _a('Editor'),
        // Module manager for content and moderation
        'manager'   => _a('Manager'),
        */
    ),

    // Front section
    'front' => array(
        // System webmaster with ultra permissions
        'webmaster' => _a('Webmaster'),
        // User
        'member'    => _a('Member'),
        // Visitor
        'guest'     => _a('Guest'),
    ),
);
