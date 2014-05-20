<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Use case
 *
 * <code>
 *  <a href="<?php echo Pi::url('www/script/download.php') . '?upload/file.ext'; ?>" title="Click to downoad">file.ext</a>
 * </code>
 */
// Pi boot with no engine bootup: current file is located in www/script/...
$boot = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/boot.php';
include $boot;

// Disable debugger message
Pi::service('log')->mute();

// Allowed file extensions
$allowedPath = array(
    rtrim(Pi::path('upload'), '/') . '/',
);

// Fetch path from query string if path is not set, i.e. through a direct request
$source = '';
if (!empty($_SERVER['QUERY_STRING'])) {
    $file = Pi::path(ltrim($_SERVER['QUERY_STRING'], '/'));
    foreach ($allowedPath as $path) {
        if (substr($file, 0, strlen($path)) == $path) {
            $source = $file;
            break;
        }
    }
}
if (empty($source) || !is_readable($source)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }
    
    return;
}

$downloader = new Pi\File\Transfer\Download;
$downloader->send($source);
