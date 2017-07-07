<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine boot definition
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

define('PI_IN_SETUP_BOOT', true);

if (!defined('PI_IN_SETUP')) {
    // Look up setup entrance
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

    // URI scheme
    $ssl = false;
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    ) {
        $scheme = 'https';
        $ssl    = true;
    } else {
        $scheme = 'http';
    }

    // URI host
    if (!empty($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        $host = $_SERVER['SERVER_NAME'];
    }

    // URI port
    $port = '';
    if (!empty($_SERVER['SERVER_PORT'])) {
        $portNum = (int) $_SERVER['SERVER_PORT'];
        if (($ssl && 443 != $portNum)
            || (!$ssl && 80 != $portNum)) {
            $port = ':' . $portNum;
        }
    }

    // Assemble redirect URI
    $redirect = sprintf(
        '%s://%s%s/%s',
        $scheme,
        $host,
        $port,
        ltrim($redirect, '/')
    );
    header('location: ' . $redirect);
}
