<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Comment posts
    'post'   => array(
        'title'         => _a('Recent comments'),
        'description'   => _a('Recent active comment posts.'),
        'render'        => array('block', 'post'),
        'template'      => 'post-list',
        'config'        => array(
            // Display limit
            'limit' => array(
                'title' => _a('Post count'),
                'description'   => _a('Number of posts to display.'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 10,
            ),
            // Module
            'module'    => array(
                'title'         => _a('Module'),
                'description'   => _a('Select module for articles.'),
                'edit'          => 'module_select',
                'value'         => ''
            ),
        ),
    ),

    // Commented articles
    'article'   => array(
        'title'         => _a('Commented articles'),
        'description'   => _a('Articles being commented.'),
        'render'        => array('block', 'article'),
        'template'      => 'article-list',
        'config'        => array(
            // Display limit
            'limit' => array(
                'title' => _a('Article count'),
                'description'   => _a('Number of articles to display.'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 10,
            ),
            // Module
            'module'    => array(
                'title'         => _a('Module'),
                'description'   => _a('Select module for articles.'),
                'edit'          => 'module_select',
                'value'         => ''
            ),
            // Order
            'order'    => array(
                'title'         => _a('Sort order'),
                'description'   => _a('Criteria to select articles.'),
                'edit'          => array(
                    'type'          => 'select',
                    'options'    => array(
                        'options'   => array(
                            'host'          => _a('Most commented'),
                            'recent'        => _a('Last commented'),
                            'publish_time'  => _a('Last published'),
                        ),
                    ),
                ),
                'value'         => 'recent'
            ),
        ),
    ),

    // Top posters
    'user'   => array(
        'title'         => _a('Top posters'),
        'description'   => _a('Users with most posts'),
        'render'        => array('block', 'user'),
        'template'      => 'user-list',
        'config'        => array(
            // Display limit
            'limit' => array(
                'title' => _a('User count'),
                'description'   => _a('Number of users to display.'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 10,
            ),
            // Uid exception
            'uid_exception'    => array(
                'title'         => _a('Excluded user ids'),
                'description'   => _a('Users to exclude, separated by ",".'),
                'edit'          => 'text',
                'value'         => '1,2',
            ),
            // Role exception
            'role_exception'    => array(
                'title'         => _a('Excluded roles'),
                'description'   => _a('Roles to exclude.'),
                'edit'          => array(
                    'type'          => 'role_select',
                    'options'    => array(
                        'is_multi'  => true,
                    ),
                ),
                'value'         => array('webmaster')
            ),
        ),
    ),
);
