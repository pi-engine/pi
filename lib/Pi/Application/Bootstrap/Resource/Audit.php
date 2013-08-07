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
use Zend\Mvc\MvcEvent;

/**
 * Audit for operations
 *
 * Options for recording:
 * - skipError: skip error action
 * - users: specific users to be logged
 * - ips: specific IPs to be logged
 * - roles: specific roles to be logged
 * - pages: specific pages to be logged
 * - methods: specific request methods to be logged
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Audit extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $events = $this->application->getEventManager();
        // Setup auditing, must go after access check whose priority is 9999
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'log'), -8000);
    }

    /**
     * Logging audit trail
     *
     * @param MvcEvent $e
     * @return void
     */
    public function log(MvcEvent $e)
    {
        // Skip if error occured
        if (!empty($this->options['skipError']) && $e->isError()) {
            return;
        }
        // Skip if response not OK
        $response = $e->getResponse();
        if ($response instanceof Response && !$response->isOk()) {
            return;
        }
        // Skip if not required method
        if (!empty($this->options['methods'])) {
            $method = $e->getRequest()->getMethod();
            if (!in_array($method, $this->options['methods'])) {
                return;
            }
        }

        $message = $e->getRequest()->isPost()
                   ? $e->getRequest()->toString()
                   : $e->getRequest()->getRequestUri();
        Pi::service('log')->audit($message);
    }
}
