<?php
/**
 * Pi Engine setup index file
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Setup
 */

// PHP 5.3+ is required for Pi Engine
if (version_compare(PHP_VERSION, '5.3.0') < 0) {
    die("PHP 5.3+ required");
}

$wizard = include './include/init.php';

$wizard->dispatch();
$wizard->render();
