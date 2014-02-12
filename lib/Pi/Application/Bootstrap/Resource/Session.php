<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * Session start
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Session extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Set options for session service
        if (!empty($this->options['service'])) {
            Pi::service('session')->setOptions($this->options['service']);
        }

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
