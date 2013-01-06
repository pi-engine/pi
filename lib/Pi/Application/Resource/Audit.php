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
use Zend\Mvc\MvcEvent;

/**
 * Audit for operations
 *
 * Options for recording:
 * skipError - skip error action
 * users - specific users to be logged
 * ips - specific IPs to be logged
 * roles - specific roles to be logged
 * pages - specific pages to be logged
 * methods - specific request methods to be logged
 */
class Audit extends AbstractResource
{
    /**
     * @return void
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

        $message = $e->getRequest()->isPost() ? $e->getRequest()->toString() : $e->getRequest()->getRequestUri();
        Pi::service('log')->audit($message);
    }
}
