<?php
/**
 * Demo module dashboard class
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

namespace Module\Demo;
use Pi;

class Dashboard
{
    /**
     * Returns summary data for admin dashboard
     *
     * @param string $module dirname for module
     * @param string $redirect redirect URI after callback
     * @return array associative array of monitoring items: title, data, callback url
     */
    public static function summary($module = null, $redirect = null)
    {
        /*
        Pi::service('i18n')->load('module/' . $module . ':monitor');
        $data = array();
        $data[] = array(
            'message'   => __('Demo app available for instruction.'),
        );
        $model = Pi::model($module . '/test');
        $count = $model->select(array('active' => 1))->count();
        if ($count > 0) {
            $data[] = array(
                'message'   => sprintf(__('%d tasks on pending.'), $count),
                'callback'  => Pi::registry("application")->getRouter()->assemble(
                    array(
                        'module'        => $module,
                        'controller'    => 'monitor',
                        'action'        => 'reset',
                        'redirect'      => $redirect,
                    ),
                    'admin'
                ),
            );
        }
         *
         */

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
