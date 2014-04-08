<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine feed application specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

$config = include __DIR__ . '/application.front.php';

// Session resource, disabled
$config['resource']['session'] = false;
// Authentication resource, disabled
$config['resource']['authentication'] = false;
// Permission resource, disabled
$config['resource']['permission'] = false;
// Rendering cache resource
$config['resource']['render_cache'] = array();

// Application service configuration
$config['application']['listeners'] = array('FeedStrategyListener');
$config['application']['view_manager']['mvc_strategies'] = null;
$config['application']['view_manager']['strategies'] = array('ViewFeedStrategy');
$config['application']['send_response'] = null;

// Application environment
//$config['config']['environment'] = 'production';

return $config;
