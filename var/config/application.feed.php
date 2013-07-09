<?php
/**
 * Pi Engine feed application specifications
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
 */

$config = include __DIR__ . '/application.front.php';
// Translations
$config['resource']['i18n'] = array(
    'translator'    => array(
        'global'    => array('usr:feed'),
        'module'    => array('feed'),
    ),
);
// Session resource, load configs from resource.session.php and instantiate session service
$config['resource']['session'] = false;
// Load authentication configs from resource.authentication.php and instantiate authentication service
$config['resource']['authentication'] = false;
// Instantiate use handler
$config['resource']['user'] = false;
// Instantiate ACL manager and register listeners
$config['resource']['acl'] = false;
// Rendering cache
$config['resource']['render'] = array(
    'page'  => true,
);

// Application service configuration
$config['application']['listeners'] = array('FeedStrategyListener');
$config['application']['view_manager']['mvc_strategies'] = null;
$config['application']['view_manager']['strategies'] = array('ViewFeedStrategy');
$config['application']['send_response'] = null;

// Application environment
//$config['config']['environment'] = 'production';

return $config;
