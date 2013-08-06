<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo;

use Pi;

class Monitor
{
    /**
     * Returns monitoring data for admin dashboard
     *
     * @param string $module dirname for module
     * @param string $redirect redirect URI after callback
     * @return array associative array of monitoring items:
     *      title, data, callback url
     */
    public static function index($module = null, $redirect = null)
    {
        $data =<<<'EOT'
        <ul>
            <li>First message in Demo.</li>
            <li>Second message in Demo.</li>
        </ul>
EOT;

        return $data;
    }
}
