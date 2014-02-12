<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

// PHP 5.3+ is required for Pi Engine
if (version_compare(PHP_VERSION, '5.3.0') < 0) {
    die('PHP 5.3+ required');
}

@set_time_limit(0);

$wizard = include __DIR__ . '/include/init.php';

$wizard->dispatch();
$wizard->render();
