<?php
/**
 * System module ACL config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

/**
 * ACL specs
 *
 *  return array(
 *      'roles' => array(
 *          'roleName'  => array(
 *              'title'     => 'Title',
 *              'parents'   => array('parent'),
 *          ),
 *          'roleNameStaff' => array(
 *              'title'     => 'Title',
 *              'parents'   => array('parent'),
 *              'section'   => 'admin',         // Default as front if not specified
 *          ),
 *          ...
 *      ),
 *      'resources' => array(
 *          // Specified module resources
 *          'module' => array(
 *              'category' => array(
 *                  //'name'          => 'category',
 *                  'title'         => 'Category Title',
 *                  'parent'        => 'parentCategory'
 *                  // Access rules
 *                  'access'        => array(
 *                      'guest'     => 1,
 *                      'member'    => 1
 *                  ),
 *                  // privilege specific rules
 *                  'privileges'    => array(
 *                      'read'      => array(
 *                          'title' => 'Read articles',
 *                      ),
 *                      'post'      => array(
 *                          'title'     => 'Post articles',
 *                          'access'    => array(
 *                              'guest'     => 0,
 *                          ),
 *                      ),
 *                      'delete'    => array(
 *                          'title'     => 'Post articles',
 *                          'access'    => array(
 *                              'guest'     => 0,
 *                              'member'    => 0,
 *                          ),
 *                      ),
 *                  ),
 *              ),
 *              ...
 *          ),
 *          // Front resources specified by controller/action
 *          'front' => array(
 *              'somename'  => array(
 *                  //'name'          => 'Name',
 *                  'controller'    => 'controllerName',
 *                  'title'         => 'Title',
 *                  'parent'        => 'parentName'
 *              ),
 *              ...
 *          ),
 *          // Front resources specified by controller/action
 *          'admin'  => array(
 *              'somename'  => array(
 *                  //'name'          => 'Name',
 *                  'controller'    => 'controllerName',
 *                  'title'         => 'Title',
 *                  'parent'        => 'parentName'
 *                  'access'         => array(
 *                      'roleA' => 1,
 *                      'roleB' => 0
 *                  ),
 *                  'privileges'    => array(
 *                      'nameA'     => array(
 *                          'title' => 'privilegeName',
 *                          'access' => array(
 *                              'roleA' => 1,
 *                              'roleB' => 0
 *                          ),
 *                      ),
 *                  ),
 *              ),
 *              ...
 *          ),
 *          ...
 *      ),
 *  );
 */

// System settings, don't change
return array(
    'roles' => array(
        /**#@+
         * Admin section
         */
        // System administrator or webmaster with ultra permissions
        'admin'     => array(
            'title'     => __('Administrator'),
            'section'   => 'admin',
        ),
        // Admin area user
        'staff'     => array(
            'title'     => __('Staff'),
            'section'   => 'admin',
        ),
        // Module/section moderator or administrator
        'moderator' => array(
            'title'     => __('Moderator'),
            'parents'   => array('staff'),
            'section'   => 'admin',
        ),
        // Content editor
        'editor' => array(
            'title'     => __('Editor'),
            'parents'   => array('staff'),
            'section'   => 'admin',
        ),
        // Module manager for content and moderation
        'manager' => array(
            'title'     => __('Manager'),
            'parents'   => array('moderator', 'editor'),
            'section'   => 'admin',
        ),
        /**#@-*/

        /**#@+
         * Front section
         */
        // User
        'member'    => array(
            'title' => __('Member')
        ),
        // Visitor
        'guest'     => array(
            'title' => __('Guest')
        ),
        // Inactive user
        'inactive'  => array(
            'title' => __('Pending')
        ),
        // Banned user
        'banned'    => array(
            'title' => __('Banned')
        ),
        /**#@-*/
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
