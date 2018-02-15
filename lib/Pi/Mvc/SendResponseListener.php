<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc;

use Pi\Mvc\ResponseSender\PhpEnvironmentResponseSender;
use Zend\Mvc\SendResponseListener as ZendSendResponseListener;
use Zend\Mvc\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Mvc\ResponseSender\SimpleStreamResponseSender;

use Zend\Mvc\ResponseSender\HttpResponseSender;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Http\PhpEnvironment\Response as PhpEnvironmentResponse;
use Zend\Mvc\MvcEvent;

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



    /**
     * Send the response
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function sendResponse(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response instanceof Response) {
            return; // there is no response to send
        }
        $event = $this->getEvent();

        // Load general config
        $configGeneral = \Pi::config('', 'system', 'general');

        /** @var PhpEnvironmentResponse $response  */

        if ($response instanceof PhpEnvironmentResponse && $response->getHeaders()->has('content-type') && \Pi::engine()->section() == 'front' && $configGeneral['minify_html_output']) {
            /** @var \Zend\Http\Header\ContentType $mediaType  */
            $mediaType = $response->getHeaders()->get('content-type');

            if($mediaType->getMediaType() == 'text/html'){
                $content = $response->getContent();
                $content = preg_replace(array("/[[:blank:]]+/"),array(' '),str_replace(array("\n","\r","\t"),'',$content));
                $response->setContent($content);
            }
        }

        $event->setResponse($response);
        $event->setTarget($this);
        $this->getEventManager()->trigger($event);
    }
}
