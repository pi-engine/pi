<?php
/**
 * Installer init file
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
 * @package         Pi\Setup
 * @version         $Id$
 */

use Pi\Setup;
require dirname(__DIR__) . '/src/Wizard.php';

// Set default timezone if not available in php.ini
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}


$wizard = new Setup\Wizard();
if (!$wizard->init()) {
    die("Pi Engine setup wizard initialization failed.");
}

// Translation function
function _t($message)
{
    return Setup\Translator::translate($message);
}

return $wizard;
