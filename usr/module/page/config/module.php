<?php
/**
 * Page module config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\Page
 * @version         $Id$
 */

/**
 * Application manifest
 */
return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => 'Page',
        // Description, for admin, optional
        'description'   => 'Page configurations for cache, blocks and ACL.',
        // Version number, required
        'version'       => '1.0.0-beta.4',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Demo site link, optional
        'demo'          => 'http://demo.xoopsengine.org/demo',

        // Module is ready for clone? Default as false
        'clonable'      => true,
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Taiwen Jiang',
        // Email address, optional
        'email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'website'   => 'http://www.xoopsengine.org',
        // Credits and aknowledgement, optional
        'credits'   => 'Zend Framework Team; Pi Engine Team; EEFOCUS Team.'
    ),
    // Maintenance actions
    'maintenance'   => array(

        // resource
        'resource' => array(
            // Database meta
            'database'  => array(
                // SQL schema/data file
                'sqlfile'   => 'sql/mysql.sql',
                // Tables to be removed during uninstall, optional - the table list will be generated automatically upon installation
                'schema'    => array(
                    'page'          => 'table',
                    'stats'         => 'table',
                )
            ),
            // Navigation definition
            'navigation'    => 'navigation.php',
            // Routes, first in last out; bigger priority earlier out
            'route'         => 'route.php',
            // View pages
            'page'          => 'page.php',
        )
    )
);
