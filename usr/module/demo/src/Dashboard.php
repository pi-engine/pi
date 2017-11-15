<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo;

use Pi;

class Dashboard
{
    /**
     * Returns summary data for admin dashboard
     *
     * @param string $module dirname for module
     * @param string $redirect redirect URI after callback
     * @return array associative array of monitoring items:
     *      title, data, callback url
     */
    public static function summary($module = null, $redirect = null)
    {
        $data = 'From Module <strong>' . $module . '</strong>';
        $data .=<<<'EOT'
        <ul>
            <li>First message in Demo.</li>
            <li>Second message in Demo.</li>
        </ul>
EOT;

        return $data;
    }
}
