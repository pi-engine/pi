<?php
/**
 * Demo module bootstrap
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
 * @category        Module
 * @package         Demo
 * @version         $Id$
 */

namespace Module\Demo;
use Pi\Application\Bootstrap as ModuleBootstrap;

class Bootstrap extends ModuleBootstrap
{
    public function bootstrap($module = null)
    {
        $message = sprintf('%s: module - %s', __METHOD__, $module);
        return $message;
    }
}
