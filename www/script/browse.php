<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Pi Engine internal file access
 *
 * @todo Enhance security by protecting files with path filters
 *      besides mimetype filters
 * @todo Add symlink like pi.url/resource/app/mvc/my.css,
 *      pi.url/resource/plugin/comment/some.js
 */

/**#@+
 * Header for big file load
 *
 * Potential values:
 * ACCEL_REDIRECT: nginx X-Accel-Redirect
 * SENDFILE: apache X-Sendfile, see https://tn123.org/mod_xsendfile/
 *
 * In order to use "X-Sendfile", both "XSendFile" and "XSendFilePath"
 * must be configured correctly.
 *
 * Note: No evidence is collected for so-called better performance yet.
 */
//define('PI_HEADER_TYPE', 'SENDFILE');
/*#@-*/

// Allowed file extensions
$allowedExtension = array('css', 'js', 'gif', 'jpg', 'png');

// Disable error_reporting
//define('APPLICATION_ENV', 'production');

// Pi boot with no engine bootup: current file is located in www/script/...
$boot = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/boot.php';
include $boot;

// Disable debugger message
Pi::service('log')->mute();

// Fetch path from query string if path is not set,
// i.e. through a direct request
if (!empty($_SERVER['QUERY_STRING'])) {
    $path = Pi::path(ltrim($_SERVER['QUERY_STRING'], '/'));
}
if (empty($path) || !is_readable($path)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }

    return;
}

/**
 * #@+
 * Any file could be reached through the browse.php if its path is known.
 * There are two possible policies to protect files:
 *  1. Only files in restricted paths are allowed to reach;
 *  2. Only files of specific mimetypes are allowed to reach.
 *
 * Currently the second policy is used and css/js/gif/jpg/png and image,
 * text files are allowed.
 */
$mimetypes = array(
     'pdf'      => 'application/pdf',
     'js'       => 'application/x-javascript',
     'swf'      => 'application/x-shockwave-flash',
     'xhtml'    => 'application/xhtml+xml',
     'xht'      => 'application/xhtml+xml',
     'xhtml'    => 'application/xml',
     'ent'      => 'application/xml-external-parsed-entity',
     'dtd'      => 'application/xml-dtd',
     'mod'      => 'application/xml-dtd',
     'bmp'      => 'image/bmp',
     'gif'      => 'image/gif',
     'jpeg'     => 'image/jpeg',
     'jpg'      => 'image/jpeg',
     'jpe'      => 'image/jpeg',
     'png'      => 'image/png',
     'tiff'     => 'image/tiff',
     'tif'      => 'image/tif',
     'wbmp'     => 'image/vnd.wap.wbmp',
     'pnm'      => 'image/x-portable-anymap',
     'pbm'      => 'image/x-portable-bitmap',
     'pgm'      => 'image/x-portable-graymap',
     'ppm'      => 'image/x-portable-pixmap',
     'xbm'      => 'image/x-xbitmap',
     'xpm'      => 'image/x-xpixmap',
     'ics'      => 'text/calendar',
     'ifb'      => 'text/calendar',
     'css'      => 'text/css',
     'html'     => 'text/html',
     'htm'      => 'text/html',
     'asc'      => 'text/plain',
     'txt'      => 'text/plain',
     'rtf'      => 'text/rtf',
     'sgml'     => 'text/x-sgml',
     'sgm'      => 'text/x-sgml',
     'tsv'      => 'text/tab-seperated-values',
     'wml'      => 'text/vnd.wap.wml',
     'wmls'     => 'text/vnd.wap.wmlscript',
     'xsl'      => 'text/xml',
);

$suffix = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$contentType = isset($mimetypes[$suffix]) ? $mimetypes[$suffix] : 'text/plain';
if (in_array($suffix, $allowedExtension)) {
} else {
    $contentTypeCategory = substr($contentType, 0, strpos($contentType, '/'));
    if (!in_array($contentTypeCategory, array('image', 'text'))) {
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            header('Status: 403 Forbidden');
        } else {
            header('HTTP/1.1 403 Forbidden');
        }

        return;
    }
}
/**#@-*/

header('Content-type: ' . $contentType);

/**
 * #@+
 */
//header('Content-Length: ' . filesize($path));
/**
 * If gzip is enabled, the filesize is not calcuated correctly
 * thus it will cause browser problems like temporarily hanging.
 * @see http://www.edginet.org/techie/website/http.html
 * A possible solution would be
 *  <code>
 *      ob_start();
 *      $obStarted = ob_start('ob_gzhandler');
 *
 *      ... output the page content...
 *
 *      if ($obStarted) ob_end_flush();  // The ob_gzhandler one
 *      header('Content-Length: '.ob_get_length());
 *      ob_end_flush();  // The main one
 *  </code>
 */
/** #@- */

if (defined('PI_HEADER_TYPE')) {
    // For nginx X-Accel-Redirect
    if ('ACCEL_REDIRECT' === PI_HEADER_TYPE) {
        header('X-Accel-Redirect: ' . $path);

        return;
    // For apache X-Sendfile
    } elseif ('SENDFILE' === PI_HEADER_TYPE) {
        header('X-Sendfile: ' . $path);

        return;
    }
}

$handle = fopen($path, 'rb');
if (!$handle) {
    return;
}
while (!feof($handle)) {
   $buffer = fread($handle, 4096);
   echo $buffer;
}
fclose($handle);
