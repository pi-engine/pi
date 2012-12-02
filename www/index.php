<?php
/**
 * Pi Engine standard application entry
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
 */

/**
 * Clean up REQUEST_URI
 * @TODO: move to dispatch
 */
if (!empty($_SERVER['REQUEST_URI']) && false !== ($pos = strpos($_SERVER['REQUEST_URI'], 'index.php'))) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $pos);
}

/**
 * Application engine type, mapped to /lib/Pi/Application/Engine, default as 'Standard'
 */
define('APPLICATION_ENGINE', 'Standard');

//Load application boot
include './boot.php';
exit();
