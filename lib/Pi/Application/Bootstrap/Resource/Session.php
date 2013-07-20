<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

class Session extends AbstractResource
{
    /**
     * {@inheritDoc}
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
