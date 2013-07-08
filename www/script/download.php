<?php
/**
 * Pi Engine download a single file
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
 * @package         Pi\Application
 */

/**
 * Use case
 * <code>
 *  <a href="<?php echo Pi::url('www/script/download.php') . '?upload/file.ext'; ?>" title="Click to downoad">file.ext</a>
 * </code>
 */
// Pi boot with no engine bootup: current file is located in www/script/...
require __DIR__ . '/../boot.php';

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
        header("Status: 404 Not Found");
    } else {
        header("HTTP/1.1 404 Not Found");
    }
    return;
}

$downloader = new Pi\File\Transfer\Download;
$downloader->send($source);
