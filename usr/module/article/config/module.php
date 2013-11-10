<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Module config and meta
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    // Module meta
    'meta'         => array(
        'title'         => _a('Article'),
        'description'   => _a('General module for content management.'),
        'version'       => '1.0.1-beta.1',
        'license'       => 'New BSD',
        'logo'          => 'image/logo.png',
        'readme'        => 'README.md',
        'clonable'      => true,
    ),
    // Author information
    'author'        => array(
        'name'          => 'Zongshu Lin',
        'email'         => 'zongshu@eefocus.com',
        'website'       => 'http://www.github.com/linzongshu',
        'credits'       => 'Pi Engine Team.'
    ),
    // Module dependency: list of module directory names, optional
    'dependency'    => array(
    ),
    // Maintenance resources
    'resource'      => array(
        'database'      => array(
            'sqlfile'      => 'sql/mysql.sql',
        ),
        // Database meta
        'navigation'    => 'navigation.php',
        'block'         => 'block.php',
        'config'        => 'config.php',
        'route'         => 'route.php',
        'permission'    => 'permission.php',
        'page'          => 'page.php',
        'comment'       => 'comment.php',
    ),
);
