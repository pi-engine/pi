<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo;

use Pi\Application\Bootstrap\ModuleBootstrap;

class Bootstrap extends ModuleBootstrap
{
    public function bootstrap($module = null)
    {
        $message = sprintf('%s: module - %s', __METHOD__, $module);

        return $message;
    }
}
