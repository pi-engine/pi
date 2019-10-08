<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
