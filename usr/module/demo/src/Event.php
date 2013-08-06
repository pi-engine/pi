<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo;

use Pi;

class Event
{
    public static function message($data, $module)
    {
        d("Called by {$module} through " . __METHOD__);
    }

    public static function selfcall($data, $module)
    {
        d("Called by {$module} through " . __METHOD__);
    }

    public static function moduleupdate($data, $module)
    {
        Pi::service('logger')->log("Called by {$module} through "
            . __METHOD__);
    }

    public static function moduleinstall($data, $module)
    {
        Pi::service('logger')->log("Called by {$module} through "
            . __METHOD__);
    }

    public static function runtime($data, $module)
    {
        Pi::service('logger')->log("Called by {$module} through "
            . __METHOD__);
    }

    public static function register($data, $module)
    {
        _e("Called by {$module} through " . __METHOD__);
    }
}
