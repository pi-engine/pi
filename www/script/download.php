<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Use case
 *
 * <code>
 *  <a href="<?php echo Pi::url('www/script/download.php') . '?upload/file.ext'; ?>" title="Click to downoad">file.ext</a>
 * </code>
 */
// Allowed file extensions
$allowedExtension = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'mp3', 'mp4'];

// Pi boot with no engine bootup: current file is located in www/script/...
$boot = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/boot.php';
include $boot;

// Disable debugger message
Pi::service('log')->mute();

// Allowed file extensions
$allowedPath = [
    rtrim(Pi::path('upload'), '/') . '/',
];

// Set query string
$queryString = _escape(htmlspecialchars(strtolower(trim($_SERVER['QUERY_STRING']))));

// Fetch path from query string if path is not set, i.e. through a direct request
$source = '';
if (isset($queryString) && !empty($queryString)) {

    // Set request file path and clean it
    $file = realpath(Pi::path(ltrim($queryString, '/')));
    foreach ($allowedPath as $path) {
        if (substr($file, 0, strlen($path)) == $path) {
            $source = $file;
            break;
        }
    }

    // Check allowed download formats
    $SplFileInfo = new SplFileInfo($source);
    $extension   = $SplFileInfo->getExtension();
    if (!in_array($extension, $allowedExtension)) {
        unset($source);
    }
}

// Set header if file not found or not allowed
if (empty($source) || !is_readable($source)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }

    echo 'File not found or Access denied!';
    return;
} else {
    $downloader = new Pi\File\Transfer\Download;
    $downloader->send($source);
}