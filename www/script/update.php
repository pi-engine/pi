<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

use Pi\Application\Installer\Module as ModuleInstaller;

/**
 * Pi Engine update script to update a module that can not be executed from normal admin
 *
 * Usage guide
 * 1. Edit `var/config/engine.php` (or `var/config/custom/engine.php` is specified), set:
 *      `'site_close'   => true`
 * 2. Execute the script `//pi.tld/script/update.php` to update system module
 * 3. Restore engine config:
 *      `'site_close' => false`
 */

// Pi boot with no engine bootup: current file is located in www/script/...
$boot = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/boot.php';
include $boot;

// Only allowed under maintenance state
if (!Pi::config('site_close')) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 403 Forbidden');
    } else {
        header('HTTP/1.1 403 Forbidden');
    }

    echo 'Access denied!';

    return;
}

// Get module and verify
$module = 'system';
if (!empty($_SERVER['QUERY_STRING'])) {
    $module = $_SERVER['QUERY_STRING'];
}
if (empty($module) || !Pi::service('module')->isActive($module)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }

    echo 'Request not found!';

    return;
}
$row = Pi::model('module')->find($module, 'name');
$installer = new ModuleInstaller;
$result = $installer->update($row);
//$details = $installer->getResult();

if ($result) {
    // Refresh caches
    Pi::service('cache')->flush();

    // Refresh assets
    $modules = Pi::registry('module')->read();
    $themes = Pi::registry('theme')->read();
    foreach (array_keys($modules) as $name) {
        $status = Pi::service('asset')->remove('module/' . $name);
        $status = Pi::service('asset')->publishModule($name);
    }
    foreach (array_keys($themes) as $name) {
        $status = Pi::service('asset')->remove('theme/' . $name);
        $status = Pi::service('asset')->publishTheme($name);
    }
    clearstatcache();

    $message = sprintf('Module %s update succeeded.', $module);
} else {
    $message = sprintf('Module %s update failed.', $module);
}

if (substr(PHP_SAPI, 0, 3) == 'cgi') {
    header('Status: 200');
} else {
    header('HTTP/1.1 200');
}

echo $message;

return;
