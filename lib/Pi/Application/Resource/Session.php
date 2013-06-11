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

use Pi;

class Session extends AbstractResource
{
    /**
     * @return void
     */
    public function boot()
    {
        try {
            // Attempt to start session
            Pi::service('session')->manager()->start();
        } catch (\Exception $e) {
            // Clear session data for current request on failure
            // Empty session for current request
            Pi::service('session')->manager()->getStorage()->clear();
            // Disconnect cookie for current user
            Pi::service('session')->manager()->expireSessionCookie();
            // Log error attempts
            if (Pi::service()->hasService('log')) {
                Pi::service('log')->audit($e->getMessage());
            }
            trigger_error($e->getMessage(), E_USER_NOTICE);
        }
        return;
    }
}
