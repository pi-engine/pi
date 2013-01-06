<?php
/**
 * Back Office run mode switch helper
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
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

class AdminSwitch extends AbstractHelper
{
    /**
     * Get back office run mode list
     *
     * @param string $module
     * @return string
     */
    public function __invoke($module = 'system')
    {
        $mode = Pi::service('session')->backoffice->mode ?: 'manage';
        $module = 'system';
        $modes = array(
            'manage'        => array(
                'label' => __('Manage'),
                'link'  => $this->view->url('admin', array(
                    'module'        => $module,
                    'controller'    => 'dashboard',
                    'mode'          => 'manage',
                )),
            ),
            'operation'     => array(
                'label' => __('Operation'),
                'link'  => $this->view->url('admin', array(
                    'module'        => $module,
                    'controller'    => 'dashboard',
                    'mode'          => 'operation',
                )),
            ),
            /*
            'deployment'    => array(
                'label' => __('Deployment'),
                'link'  => '',
            ),
            */
        );
        $content = '';
        foreach ($modes as $name => $item) {
            $content .= '<a class="btn';
            if ($name == $mode) {
                $content .= ' btn-primary';
            }
            $content .= '" href="' . $item['link'] . '">' . $this->view->escape($item['label']) . '</a>';
        }
        $content .= '<a class="btn disabled">' . $this->view->escape(__('Deployment')) . '</a>';
        return $content;
    }
}
