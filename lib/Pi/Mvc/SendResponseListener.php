<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc;

use Pi\Mvc\ResponseSender\HttpResponseSender;
use Pi\Mvc\ResponseSender\PhpEnvironmentResponseSender;
use Zend\Mvc\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Mvc\ResponseSender\SimpleStreamResponseSender;
use Zend\Mvc\SendResponseListener as ZendSendResponseListener;

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
