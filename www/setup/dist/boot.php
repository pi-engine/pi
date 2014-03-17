<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine boot definition
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

define('PI_IN_SETUP_BOOT', true);

if (!defined('PI_IN_SETUP')) {
    $script = $_SERVER['SCRIPT_NAME'];
    $filename = $_SERVER['SCRIPT_FILENAME'];
    $redirect = '';
    do {
        $script = str_replace('\\', '/', dirname($script));
        $filename = str_replace('\\', '/', dirname($filename));
        if (is_file($filename . '/boot.php') && is_dir($filename . '/setup/')) {
            $redirect = rtrim($script, '/') . '/setup/';
            break;
        }
    } while ($script);
    $redirect = sprintf(
        '%s://%s/%s',
        $_SERVER['REQUEST_SCHEME'],
        $_SERVER['HTTP_HOST'],
        ltrim($redirect, '/')
    );
    header('location: ' . $redirect);
}
