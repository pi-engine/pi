<?php
/**
 * Demo module page config
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
 * @package         Module\Demo
 * @version         $Id$
 */

return array(
    // Front section
    'front' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => 'locale',
            'title'         => __('Module homepage'),
            'controller'    => 'index',
            'action'        => 'index',
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => 'locale',
            'title'         => __('Module'),
            'controller'    => 'index',
        ),
    ),
    // Feed section
    'feed' => array(
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('Module feeds'),
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('Test feeds'),
            'controller'    => 'index',
            'action'        => 'test',
        ),
        array(
            'cache_ttl'     => 0,
            'cache_level'   => '',
            'title'         => __('Try feeds'),
            'controller'    => 'try',
        ),
    ),
);
