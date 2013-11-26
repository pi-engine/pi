<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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


// Disable error_reporting
//define('APPLICATION_ENV', 'production');

// Pi boot with no engine bootup: current file is located in www/script/...
require __DIR__ . '/../boot.php';

// Disable debugger message
//Pi::service('log')->mute();

$uid = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$size = isset($_GET['s'])
    ? $_GET['s']
    : (isset($_GET['size']) ? int($_GET['size']) : '');
$avatar = Pi::service('avatar')->get($uid, $size, false);


$mimetypes = array(
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
);

$suffix = strtolower(pathinfo($avatar, PATHINFO_EXTENSION));
$contentType = isset($mimetypes[$suffix]) ? $mimetypes[$suffix] : '';
if (!$contentType) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 403 Forbidden');
    } else {
        header('HTTP/1.1 403 Forbidden');
    }

    return;
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
        header('X-Accel-Redirect: ' . $avatar);

        return;
    // For apache X-Sendfile
    } elseif ('SENDFILE' === PI_HEADER_TYPE) {
        header('X-Sendfile: ' . $avatar);

        return;
    }
}

$handle = fopen($avatar, 'rb');
if (!$handle) {
    return;
}
while (!feof($handle)) {
   $buffer = fread($handle, 4096);
   echo $buffer;
}
fclose($handle);
