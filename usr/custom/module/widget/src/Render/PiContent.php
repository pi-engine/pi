<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Custom\Widget\Render;

use Pi;

class PiContent
{
    public static function render($options, $module = null)
    {
        $block = array(
            'subline'   => isset($options['subline'])
                ? $options['subline'] : 'custom subline',
            'github'    => isset($options['show_github'])
                ? 'Commit activities at github.' : 'custom github',
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
