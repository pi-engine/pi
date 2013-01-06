<?php
/**
 * Back Office top menu helper
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

class AdminTop extends AbstractHelper
{
    /**
     * Get back office top menu
     *
     * @param string $module
     * @return string
     */
    public function __invoke($module = 'system')
    {
        $mode = 'manage';
        if ('operation' == Pi::service('session')->backoffice->mode) {
            $mode = 'operation';
        }

        // Managed components
        if ('manage' == $mode && 'system' == $module) {
            $navConfig = Pi::service('registry')->navigation->read('system-component');
            $currentModule = Pi::service('session')->backoffice->module;
            if ($currentModule) {
                foreach ($navConfig as $key => &$nav) {
                    $nav['params']['name'] = $currentModule;
                }
            }
            //d($navConfig);
            $navigation = $this->view->navigation($navConfig);
        // Module operations
        } else {
            $modulesAllowed = Pi::service('registry')->moduleperm->read('admin');
            if (null === $modulesAllowed || in_array($module, $modulesAllowed)) {
                $navigation = $this->view->navigation($module . '-admin');
            } else {
                $navigation = $this->view->navigation(array());
            }
        }

        return $navigation;
    }
}
