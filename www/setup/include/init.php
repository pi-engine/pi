<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

use Pi\Setup;

require dirname(__DIR__) . '/src/Wizard.php';

// Set default timezone if not available in php.ini
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}


$wizard = new Setup\Wizard();
if (!$wizard->init()) {
    die('Pi Engine setup wizard initialization failed.');
}

// Translation function
function _s($message)
{
    return Setup\Translator::translate($message);
}

return $wizard;
