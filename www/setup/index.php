<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

// PHP 7.3+ is required for Pi Engine
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70300) {
    die('PHP 7.3+ is required by Pi Engine.');
}

// Security check
define('PI_IN_SETUP', true);
$bootFile = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/boot.php';
require $bootFile;
if (!defined('PI_IN_SETUP_BOOT')) {
    die('Pi Engine setup is denied.');
}

// Skip time limit for request execution
@set_time_limit(0);

// Set default timezone if not available in php.ini
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Boot with wizard
$wizardFile = dirname($_SERVER['SCRIPT_FILENAME']) . '/src/Wizard.php';
require $wizardFile;
$wizard = new Pi\Setup\Wizard();
try {
    $wizard->init();
} catch (\Exception $e) {
    $message = $e->getMessage();
    die('Pi Engine setup wizard initialization failed: ' . $e->getMessage());
}

// Translation function
function _s($message)
{
    return Pi\Setup\Translator::translate($message);
}

$wizard->dispatch();
$wizard->render();
