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
            'label'         => _a('Test User Call'),
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'user',
        ),
        'pagea'     => array(
            'label'         => _a('Homepage'),
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'index',

            'pages'         => array(
                'paginator' => array(
                    'label'         => _a('Full Paginator'),
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'page',
                ),
                'simple'    => array(
                    'label'         => _a('Lean Paginator'),
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'simple',
                ),
                'pageaa'    => array(
                    'label'         => _a('Subpage one'),
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'index',
                ),
                'pageab'    => array(
                    'label'         => _a('Subpage two'),
                    'route'         => 'default',
                    'controller'    => 'index',
                    'action'        => 'index',
                    'params'        => array(
                        'op'    => 'test',
                    ),

                    'pages'         => array(
                        'pageaba'   => array(
                            'label'         => _a('Leaf one'),
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
            'label'         => _a('Routes'),
            'route'         => 'default',
            'controller'    => 'route'
        ),
    ),
    'admin' => array(
        'pagea'     => array(
            'label'         => _t('Sample'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'route'     => array(
            'label'         => _t('Routes'),
            'route'         => 'admin',
            'controller'    => 'route',
            'action'        => 'index',
        ),
    ),
);
