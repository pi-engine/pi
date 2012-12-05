<?php
/**
 * Demo module event observer class
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
 * @package         Demo
 * @version         $Id$
 */

namespace App\Demo;

class Event
{
    public static function message($data, $module)
    {
        \Debug::e("Called by {$module} through " . __METHOD__);
    }

    public static function selfcall($data, $module)
    {
        \Debug::e("Called by {$module} through " . __METHOD__);
    }

    public static function moduleupdate($data, $module)
    {
        \Pi::service('logger')->log("Called by {$module} through " . __METHOD__);
    }

    public static function moduleinstall($data, $module)
    {
        \Pi::service('logger')->log("Called by {$module} through " . __METHOD__);
    }

    public static function runtime($data, $module)
    {
        \Pi::service('logger')->log("Called by {$module} through " . __METHOD__);
    }

    public static function register($data, $module)
    {
        \Debug::e("Called by {$module} through " . __METHOD__);
    }
}
