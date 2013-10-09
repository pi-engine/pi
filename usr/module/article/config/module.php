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
        'title'         => __('Article'),
        'description'   => __('General module for content management.'),
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
    // Maintenance actions
    'maintenance'   => array(
        'resource'      => array(
            'database'      => array(
                'sqlfile'      => 'sql/mysql.sql',
                'schema'       => array(
                    'article'       => 'table',
                    'extended'      => 'table',
                    'field'         => 'table',
                    'compiled'      => 'table',
                    'draft'         => 'table',
                    'related'       => 'table',
                    'visit'         => 'table',
                    'category'      => 'table',
                    'author'        => 'table',
                    'statistics'    => 'table',
                    'topic'         => 'table',
                    'article_topic' => 'table',
                    'level'         => 'table',
                    'user_level'    => 'table',
                    'media'         => 'table',
                    'media_statistics' => 'table',
                    'asset'         => 'table',
                    'asset_draft'   => 'table',
                ),
            ),
            // Database meta
            'navigation'    => 'navigation.php',
            'block'         => 'block.php',
            'config'        => 'config.php',
            'route'         => 'route.php',
            //'acl'           => 'acl.php',
            'page'          => 'page.php',
        ),
    ),
);
