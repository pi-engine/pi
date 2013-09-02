<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Module meta
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => __('System'),
        // Description, for admin, optional
        'description'   =>
            __('For administration of core functions of the site.'),
        // Version number, required
        'version'       => '3.2.3',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Readme file, for admin, optional
        'readme'        => 'docs/readme.txt',
        // Direct download link, available for wget, optional
        //'download'      => 'http://dl.xoopsengine.org/core',
        // Demo site link, optional
        'demo'          => 'http://pialog',

        // Module is ready for clone? Default as false
        'clonable'      => false,
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'Dev'      => 'Taiwen Jiang',
        // Email address, optional
        'Email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website'   => 'http://pialog.org',
        'Architect' => '@taiwen',
        'Front'     => '@sexnothing',
        'Design'    => '@zhangsimon, @loidco',
        // Credits and aknowledgement, optional
        'Credits'   => 'Pi Engine Team; Zend Framework Team; EEFOCUS Team.'
    ),
    // Resource
    'resource' => array(
        // Database meta
        'database'      => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        // System config
        'config'        => 'config.php',
        // ACL specs
        'acl'           => 'acl.php',
        // Block definition
        'block'         => 'block.php',
        // Event specs
        'event'         => 'event.php',
        // View pages
        'page'          => 'page.php',
        // Navigation definition
        'navigation'    => 'nav.php',
        // Routes, first in last out; bigger priority earlier out
        'route'         => 'route.php',
    ),
);
