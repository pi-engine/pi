<?php
/**
 * CAPTCHA image generator
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
 * @since           3.0
 * @package         Pi\Captcha
 * @version         $Id$
 */

// Skip engine bootup
define('PI_BOOT_SKIP', 1);
// Disable error_reporting
define('APPLICATION_ENV', 'production');

// Pi boot with no engine bootup: current file is located in www/script/...
require __DIR__ . '/../boot.php';
// Load session resource which is required by CAPTCHA
Pi::engine()->loadResource('session');

// Retrieve id generated CAPTCHA
$id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
$image = null;
if (!empty($id)) {
    // Load CAPTCA adapter
    $captcha = Pi::service('captcha')->load();
    // Generate CAPTCHA image
    $image = $captcha->createImage($id);
    // Close session
    //session_write_close();
    //Pi::service('session')->manager()->writeClose();
}

// Send responding response if image is not created
if (empty($image)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }
    return;
}

// Send image to browser
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
