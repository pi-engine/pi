<?php
/**
 * Pi Engine application specifications
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
 * @version         $Id$
 */

$config = include __DIR__ . '/application.front.php';
// Translations
$config['resource']['i18n']['translator']['global'][] = 'usr:admin';
$config['resource']['i18n']['translator']['module'][] = 'admin';

// Permission ACL
$config['resource']['acl'] = array(
    // Default access perm in case not defined
    'default'       => false,
    // If check page access
    'check_page'    => true,
    // Managed components
    'component'     => array('block', 'config', 'page', 'resource', 'event'),
    // Admin entries
    'entry'         => array('index', 'dashboard'),
);

// Render caching
$config['resource']['render'] = false;
// Audit
/*
 * Options for recording:
 * skipError - skip error action
 * users - specific users to be logged
 * ips - specific IPs to be logged
 * roles - specific roles to be logged
 * pages - specific pages to be logged
 * methods - specific request methods to be logged
 */
$config['resource']['audit'] = array(
    'skipError' => true,
    'methods'   => array('POST'),
);

// Admin mode detection
$config['resource']['adminmode'] = array();

return $config;
