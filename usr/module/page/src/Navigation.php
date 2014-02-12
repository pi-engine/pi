<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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

        $modules = Pi::registry('modulelist')->read('active');
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
