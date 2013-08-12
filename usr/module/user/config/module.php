<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => 'Test',
        // Description, for admin, optional
        'description'   => 'Test for module dependency.',
        // Version number, required
        'version'       => '1.0.0',
        // Distribution license, required
        'license'       => 'New BSD',
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Taiwen Jiang',
        // Email address, optional
        'email'     => 'taiwenjiang@tsinghua.org.cn',
    ),
    // Module dependency: list of module directory names, optional
    'dependency'    => array(
        'widget',
        'demo',
        'page',
    ),

);
