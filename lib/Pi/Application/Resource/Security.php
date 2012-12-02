<?php
/**
 * Bootstrap resource
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
 * @package         Pi\Application
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Resource;

use Pi\Security as SecurityUtility;

class Security extends AbstractResource
{
    /**
     * Check security settings
     *
     * Policy: quit the process and approve current request if TRUE is returned by a check; quit and deny the request if FALSE is return; continue with next check if NULL is returned
     */
    public function boot()
    {
        $options = $this->options;
        foreach ($options as $type => $opt) {
            if (empty($opt)) {
                continue;
            }
            $status = SecurityUtility::$type($opt);
            if ($status) return true;
            if (false === $status) {
                return false;
                SecurityUtility::deny($type);
            }
        }
    }
}
