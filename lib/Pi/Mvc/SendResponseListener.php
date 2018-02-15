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
use TinyHtmlMinifier\TinyHtmlMinifier;
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
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, array($this, 'outputCompress'), 100);
    }


    public function outputCompress($e)
    {
        $response = $e->getResponse();

        // Load general config
        $configGeneral = \Pi::config('', 'system', 'general');

        /** @var PhpEnvironmentResponse $response  */

        if ($response instanceof PhpEnvironmentResponse && $response->getHeaders()->has('content-type') && \Pi::engine()->section() == 'front' && $configGeneral['minify_html_output']) {
            $response->setContent($this->_compress($response->getBody()));
        }
    }

    private function _compress($content)
    {
        $minifier = new TinyHtmlMinifier(array());
        $content = $minifier->minify($content);


        $search = array(
            '/\>[^\S ]+/s',         //strip whitespaces after tags, except space
            '/[^\S ]+\</s',         //strip whitespaces before tags, except space
            '/(\s)+/s',             // shorten multiple whitespace sequences
//            '/<!--(.|\s)*?-->/',    //strip HTML comments
            '#(?://)?<!\[CDATA\[(.*?)(?://)?\]\]>#s', //leave CDATA alone
        );
        $replace = array(
            '>',
            '<',
            '\\1',
//            '',
            "//<![CDATA[\n".'\1'."\n//]]>",
        );
        $content = preg_replace($search, $replace, $content);

        return $content;
    }
}
