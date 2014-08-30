<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Custom route config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'article' => array(
        'section'  => 'front',
        'priority' => 100,

        'type'      => 'Module\Article\Route\Article',
        'options'   => array(
            'structure_delimiter'   => '/',
            'param_delimiter'       => '/',
            'key_value_delimiter'   => '-',
            //'prefix'                => '/article',
            'defaults'              => array(
                'module'        => 'article',
                'controller'    => 'index',
                'action'        => 'index',
            ),
        ),
    ),
);
