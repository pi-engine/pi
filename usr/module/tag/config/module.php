<?php
/**
 * Tag module config
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
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @version         $Id$
 */

/**
 * Application manifest
 */
return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => 'Tags',
        // Description, for admin, optional
        'description'   => 'Tag',
        // Version number, required
        'version'       => '1.0.0-beta.1',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Chuang Liu',
        // Email address, optional
        'email'     => 'liuchuang@eefocus.com',
    ),
    // Module dependency: list of module directory names, optional
    'maintenance'   => array(
        // resource
        'resource' => array(
            // Database meta
            'database'  => array(
                // SQL schema/data file
                'sqlfile'   => 'sql/mysql.sql',
                // Tables to be removed during uninstall, optional - the table list will be generated automatically upon installation
                'schema'    => array(
                    'tag'          => 'table',
                    'link'         => 'table',
                    'stats'        => 'table',
                ),
            ),
            // Navigation definition
            'navigation' => 'navigation.php',
            // Config definition.
            'config'     => 'config.php',
            // Block definition.
            'block'      => 'block.php',
        )
    )
);
