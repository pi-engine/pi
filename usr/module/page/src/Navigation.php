<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page;

use Pi;

class Navigation
{
    public static function modules($module)
    {
        $nav = [
            'pages' => [],
        ];

        $modules = Pi::registry('modulelist')->read('active');
        unset($modules['system']);
        foreach ($modules as $key => $data) {
            $nav['pages'][$key] = [
                'label'  => $data['title'],
                'module' => $key,
                'route'  => 'admin',
            ];
        }

        return $nav;
    }
}
