<?php
/**
 * Module page config
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
 * @package         Module\Widget
 * @version         $Id$
 */

return array(
    // Admin section
    'admin' => array(
        array(
            'controller'    => 'script',
            'permission'    => array(
                'parent'        => 'script',
            ),
        ),
        array(
            'controller'    => 'static',
            'permission'    => array(
                'parent'        => 'static',
            ),
        ),
        array(
            'controller'    => 'carousel',
            'permission'    => array(
                'parent'        => 'carousel',
            ),
        ),
        array(
            'controller'    => 'tab',
            'permission'    => array(
                'parent'        => 'tab',
            ),
        ),
    ),
);
