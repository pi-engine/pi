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
 *              'parents'   => array('parent')
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
 *                  'rules'         => array(
 *                      'guest'     => 1,
 *                      'member'    => 1
 *                  ),
 *                  // privilege specific rules
 *                  'privileges'    => array(
 *                      'read'      => array(
 *                          'title' => 'Read articles',
 *                      ),
 *                      'post'      => array(
 *                          'title' => 'Post articles',
 *                          'rules' => array(
 *                              'guest'     => 0,
 *                          ),
 *                      ),
 *                      'delete'    => array(
 *                          'title' => 'Post articles',
 *                          'rules' => array(
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
 *                  'rules'         => array(
 *                      'roleA' => 1,
 *                      'roleB' => 0
 *                  ),
 *                  'privileges'    => array(
 *                      'nameA'     => array(
 *                          'title' => 'privilegeName',
 *                          'rules' => array(
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
        // System administrator or webmaster
        'admin'     => array('title'    => __('Administrator')),
        // User
        'member'    => array('title'    => __('Member')),
        // Visitor
        'guest'     => array('title'    => __('Guest')),
        // Inactive user
        'inactive'  => array('title'    => __('Pending user')),
        // Banned user
        'banned'    => array('title'    => __('Banned account')),
        // Module/section moderator or administrator
        'moderator' => array(
            'title'     => __('Moderator'),
            'parents'   => array('member')
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
            // global moderate
            'moderate'  => array(
                'module'        => 'system',
                'title'         => __('Moderated area'),
                'access'        => array(
                    'guest'     => 0,
                    'moderator' => 1,
                ),
            ),
        ),
        // Admin section
        'admin' => array(
            // Basic admin resource
            'admin'     => array(
                'title'         => __('Global admin resource')
            ),

            // System specs
            // configurations
            'config'    => array(
                'title'         => __('Configuration management')
            ),
            // modules
            'module'    => array(
                'title'         => __('Module management')
            ),
            // appearance
            'appearance'    => array(
                'title'         => __('Appearance management')
            ),
            // permissions
            'permission'    => array(
                'title'         => __('Permission management')
            ),
            // maintenance
            'maintenance'   => array(
                'title'         => __('Maintenance')
            ),
        ),
        // Module resources
        'module'    => array(
            // test
            array(
                'name'          => 'test',
                'title'         => __('Test resource'),
                /*
                'access'    => array(
                    'guest'     => 1,
                    'member'    => 1,
                ),
                */
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
            array(
                'name'          => 'test2',
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
    ),
);
