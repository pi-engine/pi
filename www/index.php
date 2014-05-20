<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine home entry
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

/**
 * Clean up REQUEST_URI
 * @TODO: move to dispatch
 */
if (!empty($_SERVER['REQUEST_URI'])
    && false !== ($pos = strpos($_SERVER['REQUEST_URI'], 'index.php'))) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $pos);
}

/**
 * Application engine type, mapped to /lib/Pi/Application/Engine,
 * default as 'Standard'
 */
define('APPLICATION_ENGINE', 'Standard');
define('PI_BOOT_ENABLE', 1);

//Load application boot
include realpath('./boot.php');
exit();
