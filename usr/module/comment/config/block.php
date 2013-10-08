<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // Comment posts
    'post'   => array(
        'title'         => __('Recent comments'),
        'description'   => __('Recent active comment posts.'),
        'render'        => array('block', 'post'),
        'template'      => 'post-list',
        'config'        => array(
            // Display limit
            'limit' => array(
                'title' => __('Post count'),
                'description'   => __('Number of posts to display.'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 10,
            ),
            // Module
            'module'    => array(
                'title'         => __('Module'),
                'description'   => __('Select module for articles'),
                'edit'          => 'module_select',
                'value'         => ''
            ),
        ),
    ),

    // Commented articles
    'article'   => array(
        'title'         => __('Commented articles'),
        'description'   => __('Articles being commented.'),
        'render'        => array('block', 'article'),
        'template'      => 'article-list',
        'config'        => array(
            // Display limit
            'limit' => array(
                'title' => __('Article count'),
                'description'   => __('Number of articles to display.'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 10,
            ),
            // Module
            'module'    => array(
                'title'         => __('Module'),
                'description'   => __('Select module for articles.'),
                'edit'          => 'module_select',
                'value'         => ''
            ),
            // Order
            'order'    => array(
                'title'         => __('Sort order'),
                'description'   => __('Criteria to select articles.'),
                'edit'          => array(
                    'type'          => 'select',
                    'options'    => array(
                        'options'   => array(
                            'host'          => __('Most commented'),
                            'recent'        => __('Last commented'),
                            'publish_time'  => __('Last published'),
                        ),
                    ),
                ),
                'value'         => 'recent'
            ),
        ),
    ),

    // Top posters
    'user'   => array(
        'title'         => __('Top posters'),
        'description'   => __('Users with most posts'),
        'render'        => array('block', 'user'),
        'template'      => 'user-list',
        'config'        => array(
            // Display limit
            'limit' => array(
                'title' => __('User count'),
                'description'   => __('Number of users to display.'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 10,
            ),
            // Uid exception
            'uid_exception'    => array(
                'title'         => __('Excluded user ids'),
                'description'   => __('Users to exclude, separated by ",".'),
                'edit'          => 'text',
                'value'         => '1,2',
            ),
            // Role exception
            'role_exception'    => array(
                'title'         => __('Excluded roles'),
                'description'   => __('Roles to exclude.'),
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
