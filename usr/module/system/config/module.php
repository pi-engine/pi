<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        'title'         => _a('System'),
        // Description, for admin, optional
        'description'   =>
            _a('For administration of core functions of the site.'),
        // Version number, required
        'version'       => '3.5.0',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Readme file, for admin, optional
        'readme'        => 'docs/readme.txt',
        // Direct download link, available for wget, optional
        //'download'      => 'http://dl.xoopsengine.org/core',
        // Demo site link, optional
        'demo'          => 'http://pialog.org',

        // Module is ready for clone? Default as false
        'clonable'      => false,
        //font-awesome: http://fontawesome.io/icons/
        'icon'          => 'fa-tachometer'
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'Dev'      => 'Taiwen Jiang; Liu Chuang; Liaowei; Zongshu Lin',
        'Architect' => '@taiwen',
        'UI/UE'     => '@zhangsimon, @loidco, @voltan, Zeng Long',
        'QA'        => '@MarcoXoops, Zhang Hua, @lavenderli',
        // Email address, optional
        'Email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website'   => 'http://pialog.org',
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
        // Permission specs
        'permission'    => 'permission.php',
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
