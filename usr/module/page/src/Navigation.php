<?php
/**
 * Pi custom navigation content generator
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
 * @package         Module\System
 * @version         $Id$
 */

namespace Module\Page;

use Pi;

class Navigation
{
    public static function modules($module)
    {
        $nav = array(
            'pages'     => array(),
        );

        $modules = Pi::service('registry')->modulelist->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $nav['pages'][$key] = array(
                'label'     => $data['title'],
                'module'    => $key,
                'route'     => 'admin',
            );
        }

        return $nav;
    }
}
