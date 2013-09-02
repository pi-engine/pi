<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Permission ACL specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'roles' => array(
        // Admin section
        'admin' => array(
            // System administrator or webmaster with ultra permissions
            'admin'     => array(
                'title'     => __('Administrator'),
            ),
            // Admin area user
            'staff'     => array(
                'title'     => __('Staff'),
            ),
            // Module/section moderator or administrator
            'moderator' => array(
                'title'     => __('Moderator'),
                'parents'   => array('staff'),
            ),
            // Content editor
            'editor' => array(
                'title'     => __('Editor'),
                'parents'   => array('staff'),
            ),
            // Module manager for content and moderation
            'manager' => array(
                'title'     => __('Manager'),
                'parents'   => array('moderator', 'editor'),
            ),
        ),

        // Front section
        'front' => array(
            // System administrator with ultra permissions
            'webmaster' => array(
                'title'     => __('Webmaster'),
                'parents'   => array('member'),
            ),
            // User
            'member'    => array(
                'title' => __('Member')
            ),
            // Visitor
            'guest'     => array(
                'title' => __('Guest')
            ),
            // Inactive user
            'pending'  => array(
                'title' => __('Pending')
            ),
            // Banned user
            'disabled'    => array(
                'title' => __('Disabled')
            ),
        ),
    ),

    'resources' => array(
        // Front section
        'front' => array(
            // global public
            'public'    => array(
                'module'        => 'system',
                'title'         => __('Global public resource'),
                'access'        => array(
                    'guest'     => 1,
                    'member'    => 1,
                ),
            ),
            // global guest
            'guest' => array(
                'module'        => 'system',
                'title'         => __('Guest only'),
                'access'        => array(
                    'guest'     => 1,
                    'member'    => 0,
                ),
            ),
            // global member
            'member'    => array(
                'module'        => 'system',
                'title'         => __('Member only'),
                'access'        => array(
                    'guest'     => 0,
                    'member'    => 1,
                ),
            ),
            /*
            // global moderate
            'moderate'  => array(
                'module'        => 'system',
                'title'         => __('Moderated area'),
                'access'        => array(
                    'guest'     => 0,
                    'moderator' => 1,
                ),
            ),
            */
        ),
        // Admin section
        'admin' => array(
            // Generic admin resource
            'admin'     => array(
                'title'         => __('Global admin permission'),
                'access'        => array(
                    'staff'     => 1,
                ),
            ),

            // Managed components
            // Configurations
            'config'    => array(
                'title'         => __('Component: configurations'),
                'access'        => array(
                    'staff'     => 0,
                    'moderator' => 1,
                ),
            ),
            // Block content and permission
            'block'     => array(
                'title'         => __('Component: blocks'),
                'access'        => array(
                    'staff'     => 0,
                    'moderator' => 1,
                ),
            ),
            // Page dress up, cache and permission
            'page'     => array(
                'title'         => __('Component: pages'),
                'access'        => array(
                    'staff'     => 0,
                    'moderator' => 1,
                ),
            ),
            // Resource permissions
            'resource'  => array(
                'title'         => __('Component: resources'),
                'access'        => array(
                    'staff'     => 0,
                    'admin'     => 1,
                ),
            ),
            // Event hooks
            'event'     => array(
                'title'         => __('Component: events/hooks'),
                'access'        => array(
                    'staff'     => 0,
                    'moderator' => 1,
                ),
            ),

            // System operations
            // Modules
            'module'    => array(
                'title'         => __('Operation: modules'),
                'access'        => array(
                    'staff'     => 0,
                    'manager'   => 1,
                ),
            ),
            // Themes
            'theme'    => array(
                'title'         => __('Operation: themes'),
                'access'        => array(
                    'staff'     => 0,
                    'editor'    => 1,
                ),
            ),
            // Navigations
            'navigation'    => array(
                'title'         => __('Operation: navigatons'),
                'access'        => array(
                    'staff'     => 0,
                    'editor'    => 1,
                ),
            ),
            // Roles
            'role'    => array(
                'title'         => __('Operation: roles'),
                'access'        => array(
                    'staff'     => 0,
                    'admin'     => 1,
                ),
            ),
            // Permissions
            'perm'    => array(
                'title'         => __('Operation: permissions'),
                'access'        => array(
                    'staff'     => 0,
                    'admin'     => 1,
                ),
            ),
            // Members
            'member'    => array(
                'title'         => __('Operation: members'),
                'access'        => array(
                    'staff'     => 0,
                    'admin'     => 1,
                ),
            ),
            // maintenance
            'maintenance'   => array(
                'title'         => __('Operation: maintenance'),
                'access'        => array(
                    'staff'     => 0,
                    'manager'   => 1,
                ),
            ),
        ),

        /*
        // Module resources
        'module'    => array(
            // test
            'test'  => array(
                //'name'          => 'test',
                'title'         => __('Test resource'),
                'privileges'    => array(
                    'read'  => array(
                        'title' => __('Read privilege'),
                        'access'    => array(
                            'guest'     => 1,
                            'member'    => 1,
                        )
                    ),
                    'write'  => array(
                        'title' => __('Write privilege'),
                        'access'    => array(
                            'guest'     => 0,
                            'member'    => 1,
                        )
                    ),
                    'manage'  => array(
                        'title' => __('Management privilege'),
                        'access'    => array(
                            'guest'     => 0,
                            'moderator' => 1,
                        )
                    ),
                )
            ),
            // second test
            'test2'  => array(
                //'name'          => 'test2',
                'title'         => __('Test resource 2'),
                'privileges'    => array(
                    'read'  => array(
                        'title' => __('Read privilege 2'),
                        'access'    => array(
                            'guest'     => 0,
                            'member'    => 1,
                        )
                    ),
                    'write'  => array(
                        'title' => __('Write privilege 2'),
                        'access'    => array(
                            'guest'     => 0,
                        )
                    ),
                    'manage'  => array(
                        'title' => __('Management privilege 2'),
                        'access'    => array(
                            'guest'     => 0,
                            'moderator' => 1,
                        )
                    ),
                )
            ),
        ),
        */
    ),
);
