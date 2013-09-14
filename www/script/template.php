<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Pi Engine template file access
 *
 * Sample code:
 *
 * ```
 *  // Suppose template file is available at:
 *  // `module/<module-name>/asset/template/<template-name>.phtml`
 *  $templatePath = Pi::asset()->getAssetPath(
 *      'module/<module-name>',
 *      'template/<template-name>.phtml'
 *  );
 *
 *  $templateUrl = Pi::url('script/template.php?' . $templatePath);
 *
 *  // With specific module translation
 *  $templateUrl = Pi::url('script/template.php?module=<module-name>&path=' . $templatePath);
 *
 *  // With specific domain translation
 *  $templateUrl = Pi::url('script/template.php?domain=<domain-name>&path=' . $templatePath);
 * ```
 */


// Allowed file extension
$allowedExtension = 'phtml';

// Pi boot with no engine bootup: current file is located in www/script/...
require __DIR__ . '/../boot.php';

// Disable debugger message
Pi::service('log')->mute();

// Load i18n resource which is required by template
Pi::engine()->bootResource('i18n');

if (isset($_GET['module']) && Pi::service('module')->isActive($_GET['module'])) {
    Pi::service('i18n')->loadModule($_GET['module']);
}
if (isset($_GET['domain'])) {
    Pi::service('i18n')->load($_GET['domain']);
}
if (isset($_GET['path'])) {
    $path = $_GET['path'];
} elseif (!empty($_SERVER['QUERY_STRING'])) {
    $path = $_SERVER['QUERY_STRING'];
}
if ($path) {
    $path = Pi::path(ltrim($path, '/'));
}

if (empty($path) || !is_readable($path)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }

    return;
}

$suffix = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if ($suffix != $allowedExtension) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 403 Forbidden');
    } else {
        header('HTTP/1.1 403 Forbidden');
    }

    return;
}

header('Content-type: text/html');

include $path;