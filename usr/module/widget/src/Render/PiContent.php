<?php
/**
 * Widget renderer
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
 * @since           1.0
 * @package         Module\Widget
 * @version         $Id$
 */

namespace Module\Widget\Render;

use Pi;

class PiContent
{
    public static function render($options, $module = null)
    {
        $block = array(
            'subline'   => isset($options['subline']) ? $options['subline'] : 'custom subline',
            'github'    => isset($options['show_github']) ? 'Commit activities at github.' : 'custom github',
        );
        return $block;
    }

    public static function test($options, $module = null)
    {
        $block = array(
            'subline'   => 'test',
            'github'    => 'test',
        );
        return $block;
    }

}
