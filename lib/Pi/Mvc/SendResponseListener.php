<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc;

use Pi\Mvc\ResponseSender\PhpEnvironmentResponseSender;
use Pi\Mvc\ResponseSender\HttpResponseSender;
use Zend\Mvc\SendResponseListener as ZendSendResponseListener;
use Zend\Mvc\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Mvc\ResponseSender\SimpleStreamResponseSender;

class SendResponseListener extends ZendSendResponseListener
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new PhpEnvironmentResponseSender(), -1000);
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new ConsoleResponseSender(), -2000);
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new SimpleStreamResponseSender(), -3000);
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new HttpResponseSender(), -4000);
    }
}
