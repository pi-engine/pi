<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    //'translate' => 'navigation',
    'front'   => array(
        'tree'     => array(
            'label'         => 'Test User Call',
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'user',
        ),
        'pagea'     => array(
            'label'         => 'Homepage',
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'index',

            'pages'         => array(
                'paginator' => array(
                    'label'         => 'Full Paginator',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'page',
                ),
                'simple'    => array(
                    'label'         => 'Lean Paginator',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'simple',
                ),
                'pageaa'    => array(
                    'label'         => 'Subpage one',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'index',
                ),
                'pageab'    => array(
                    'label'         => 'Subpage two',
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'index',
                    'params'        => array(
                        'op'    => 'test',
                    ),

                    'pages'         => array(
                        'pageaba'   => array(
                            'label'         => 'Leaf one',
                            'route'         => 'default',
                            'controller'    => 'index',
                            'action'        => 'index',
                            'params'        => array(
                                'op'    => 'test',
                                'page'  => 2,
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'route' => array(
            'label'         => 'Routes',
            'route'         => 'default',
            'controller'    => 'route'
        ),
    ),
    'admin' => array(
        'pagea'     => array(
            'label'         => 'Sample',
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'route'     => array(
            'label'         => 'Routes',
            'route'         => 'admin',
            'controller'    => 'route',
            'action'        => 'index',
        ),
    ),
);
