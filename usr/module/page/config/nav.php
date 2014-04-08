<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'meta'  => array(
        'site'  => array(
            'title'     => _t('Custom site navigation'),
            'section'   => 'front',
        ),
    ),
    'item'  => array(
        'front'     => false,
        'admin'     => array(
            'list'     => array(
                'label'         => _t('Page list'),
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'index',

                'pages' => array(
                    'edit'   => array(
                        'label'         => _t('Edit page'),
                        'route'         => 'admin',
                        'controller'    => 'index',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                    'delete'   => array(
                        'label'         => _t('Delete page'),
                        'route'         => 'admin',
                        'controller'    => 'index',
                        'action'        => 'delete',
                        'visible'       => 0,
                    ),
                ),
            ),
            'add'   => array(
                'label'         => _t('Add a page'),
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'add',
            ),
            'template'  => array(
                'label'         => _t('Template list'),
                'route'         => 'admin',
                'controller'    => 'template',
                'action'        => 'index',
            ),
        ),

        // Custom navigation
        'site' => array(
            'home'     => array(
                'label'         => _a('Home'),
                'route'         => 'home',
            ),
            'about'     => array(
                'label'         => _a('About us'),
                'route'         => '.page',
                'action'        => 'about',
            ),
            'contact'     => array(
                'label'         => _a('Contact us'),
                'route'         => '.page',
                'action'        => 'contact',
            ),
            'term'     => array(
                'label'         => _a('Terms of use'),
                'route'         => '.page',
                'action'        => 'terms',
            ),
            'privacy'     => array(
                'label'         => _a('Privacy guidelines'),
                'route'         => '.page',
                'action'        => 'privacy',
            ),
            'join'     => array(
                'label'         => _a('Join us'),
                'route'         => 'user',
            ),
            'app'       => array(
                'label'         => _a('Applications'),
                'uri'           => '',
                'callback'      => 'Module\\Page\\Navigation::modules',
            ),
            'eefocus'   => array(
                'label'         => _a('Pi Engine'),
                'uri'           => 'http://pialog.org',
                'target'        => '_blank',
            ),
        ),
    ),
);
