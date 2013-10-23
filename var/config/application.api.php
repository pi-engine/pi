<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine API application specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

$config = include __DIR__ . '/application.front.php';

// Security resource, load configs from resource.security.php
$config['resource']['security'] = array(
    // IP check: deny 'bad' IPs, approve 'good' IPs
    'ip'        => true,

    // Super GLOBALS
    'globals'   => true,

    // XSS check
    'xss'       => true,

    // Enable DoS protection on HTTP_USER_AGENT
    'dos'       => false,

    // crawl bots protection on HTTP_USER_AGENT
    'bot'       => false,
);

// Translations
$config['resource']['i18n'] = array(
    'translator'    => array(
        'global'    => array('usr:api'),
        'module'    => array('api'),
    ),
);
// Session resource, load configs from resource.session.php and instantiate session service
$config['resource']['session'] = false;
// Load authentication configs from resource.authentication.php and instantiate authentication service
$config['resource']['authentication'] = false;
// Instantiate use handler
$config['resource']['user'] = false;
// Instantiate permission manager and register listeners
$config['resource']['permission'] = false;
// Rendering cache
$config['resource']['render_cache'] = array(
    'page'  => true,
);

// Application service configuration
$config['application']['listeners'] = array('ApiStrategyListener');
$config['application']['view_manager']['mvc_strategies'] = null;
$config['application']['view_manager']['strategies'] = array('ViewJsonStrategy');
$config['application']['send_response'] = null;

// Application environment
//$config['config']['environment'] = 'production';

return $config;
