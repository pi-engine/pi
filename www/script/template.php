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
 *  $templatePath = 'template/<template-name>.phtml';
 *
 *  // Module template with module translation
 *  $templateUrl = Pi::url('script/template.php?module=<module-name>&path=' . $templatePath);
 *
 *  // Theme template with theme translation
 *  $templateUrl = Pi::url('script/template.php?theme=<theme-name>&path=' . $templatePath);
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

$path = '';
if (isset($_GET['path'])) {
    $path = ltrim(htmlspecialchars($_GET['path']), '/');
}

if ($path) {
    if (isset($_GET['module'])) {
        $module = htmlspecialchars($_GET['module']);
        Pi::service('i18n')->loadModule('main', $module);
        $path = Pi::asset()->getAssetPath('module/' . $module, $path);
    } elseif (isset($_GET['theme'])) {
        $theme = htmlspecialchars($_GET['theme']);
        Pi::service('i18n')->loadTheme('main', $theme);
        $path = Pi::asset()->getAssetPath('theme/' . $theme, $path);
    }
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