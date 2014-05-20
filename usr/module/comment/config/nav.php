<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'front' => array(
        'list'      => array(
            'route'         => 'comment',
            'label'         => _a('All comments'),
            'controller'    => 'list',
            'action'        => 'index',
        ),
        'article'      => array(
            'route'         => 'comment',
            'label'         => _a('Commented articles'),
            'controller'    => 'list',
            'action'        => 'article',
        ),
        'my-post'   => array(
            'route'         => 'comment',
            'label'         => _a('My comments'),
            'controller'    => 'list',
            'action'        => 'user',
            'params'        => array(
                'my'    => 1,
            ),
        ),
        'my-received'   => array(
            'route'         => 'comment',
            'label'         => _a('My received'),
            'controller'    => 'list',
            'action'        => 'received',
            'params'        => array(
                'my'    => 1,
            ),
        ),
        'my-article'    => array(
            'route'         => 'comment',
            'label'         => _a('My articles'),
            'controller'    => 'list',
            'action'        => 'article',
            'params'        => array(
                'my'    => 1,
            ),
        ),
    ),
    'admin'   => array(
        'portal'     => array(
            'route'         => 'admin',
            'label'         => _t('Portal'),
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'list'     => array(
            'route'         => 'admin',
            'label'         => _t('All comments'),
            'controller'    => 'list',
            'action'        => 'index',
            'params'        => array(
                'active'    => -1,
            ),

            'pages'         => array(
                'all'     => array(
                    'route'         => 'admin',
                    'label'         => _t('Active posts'),
                    'controller'    => 'list',
                    'action'        => 'index',
                    'params'        => array(
                        'active'    => -1,
                    ),
                ),
                'active'     => array(
                    'route'         => 'admin',
                    'label'         => _t('Active posts'),
                    'controller'    => 'list',
                    'action'        => 'index',
                    'params'        => array(
                        'active'    => 1,
                    ),
                ),
                'inactive'     => array(
                    'route'         => 'admin',
                    'label'         => _t('Inactive posts'),
                    'controller'    => 'list',
                    'action'        => 'index',
                    'params'        => array(
                        'active'    => 0,
                    ),
                ),
            ),
        ),
        'type'  => array(
            'route'         => 'admin',
            'label'         => _t('Types'),
            'controller'    => 'type',
            'action'        => 'index',
        ),
        'module'    => array(
            'route'         => 'admin',
            'label'         => _t('By module'),
            'controller'    => 'list',
            'action'        => 'module',
        ),
        'user'      => array(
            'route'         => 'admin',
            'label'         => _t('By user'),
            'controller'    => 'list',
            'action'        => 'user',
        ),
        'article'    => array(
            'route'         => 'admin',
            'label'         => _t('By article'),
            'controller'    => 'list',
            'action'        => 'article',
            'params'        => array(
                'active'    => -1,
            ),

            'pages'         => array(
                'all'     => array(
                    'route'         => 'admin',
                    'label'         => _t('Article with comments'),
                    'controller'    => 'list',
                    'action'        => 'article',
                    'params'        => array(
                        'active'    => -1,
                    ),
                ),
                'active'     => array(
                    'route'         => 'admin',
                    'label'         => _t('Article with active comments'),
                    'controller'    => 'list',
                    'action'        => 'article',
                    'params'        => array(
                        'active'    => 1,
                    ),
                ),
            ),
        ),
    ),
);
