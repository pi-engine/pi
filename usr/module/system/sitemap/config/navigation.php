<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
return array(
    'admin' => array(
        'generat' => array(
            'label'         => _a('Generate'),
            'route'         => 'admin',
            'module'        => 'sitemap',
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'top' => array(
            'label'         => _a('Top links'),
            'route'         => 'admin',
            'module'        => 'sitemap',
            'controller'    => 'index',
            'action'        => 'top',
        ),
        'list' => array(
            'label'         => _a('List links'),
            'route'         => 'admin',
            'module'        => 'sitemap',
            'controller'    => 'index',
            'action'        => 'list',
        ),
    ),
);