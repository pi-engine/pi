<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'meta'  => array(
        'site'  => array(
            'title'     => 'Custom site navigation',
            'section'   => 'front',
        ),
    ),
    'item'  => array(
        'front'     => false,
        'admin'     => array(
            'list'     => array(
                'label'         => 'Page list',
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'index',

                'pages' => array(
                    'edit'   => array(
                        'label' => 'Edit page',
                        'route'         => 'admin',
                        'controller'    => 'index',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                    'delete'   => array(
                        'label' => 'Delete page',
                        'route'         => 'admin',
                        'controller'    => 'index',
                        'action'        => 'delete',
                        'visible'       => 0,
                    ),
                ),
            ),
            'add'   => array(
                'label' => 'Add a page',
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'add',
            ),
        ),

        // Custom navigation
        'site' => array(
            'home'     => array(
                'label'         => 'Home',
                'route'         => 'home',
            ),
            'about'     => array(
                'label'         => 'About us',
                'route'         => '.page',
                'action'        => 'about',
            ),
            'contact'     => array(
                'label'         => 'Contact us',
                'route'         => '.page',
                'action'        => 'contact',
            ),
            'term'     => array(
                'label'         => 'Terms of use',
                'route'         => '.page',
                'action'        => 'terms',
            ),
            'privacy'     => array(
                'label'         => 'Privacy guidelines',
                'route'         => '.page',
                'action'        => 'privacy',
            ),
            'join'     => array(
                'label'         => 'Join us',
                'route'         => 'user',
            ),
            'app'       => array(
                'label'         => 'Applications',
                'uri'           => '',
                'callback'      => 'Module\\Page\\Navigation::modules',
            ),
            'eefocus'   => array(
                'label'         => 'Pi Engine',
                'uri'           => 'http://pialog.org',
                'target'        => '_blank',
            ),
        ),
    ),
);
