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
use Laminas\Mvc\SendResponseListener as LaminasSendResponseListener;
use Laminas\Mvc\ResponseSender\ConsoleResponseSender;
use Laminas\Mvc\ResponseSender\SendResponseEvent;
use Laminas\Mvc\ResponseSender\SimpleStreamResponseSender;
use Laminas\Mvc\ResponseSender\HttpResponseSender;
use Laminas\Stdlib\ResponseInterface as Response;
use Laminas\Http\PhpEnvironment\Response as PhpEnvironmentResponse;
use Laminas\Mvc\MvcEvent;
use Nguyenhiep\Minifier\TinyHtmlMinifier;

class SendResponseListener extends LaminasSendResponseListener
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
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, [$this, 'outputCompress'], 100);
    }

    public function outputCompress($e)
    {
        $response = $e->getResponse();

        // Load general config
        $configGeneral = \Pi::config('', 'system', 'general');

        /** @var PhpEnvironmentResponse $response */
        if ($response instanceof PhpEnvironmentResponse && $response->getHeaders()->has('content-type') && \Pi::engine()->section() == 'front'
            && $configGeneral['minify_html_output']
        ) {

            /**
             * Only public pages
             */
            if (!\Pi::user()->getId()) {
                $response->setContent($this->_compress($response->getBody()));
            }
        }
    }

    private function _compress($content)
    {
        $minifier = new TinyHtmlMinifier([]);
        $content  = $minifier->minify($content);


        $search = [
            '/\>[^\S ]+/s',         //strip whitespaces after tags, except space
            '/[^\S ]+\</s',         //strip whitespaces before tags, except space
            '/(\s)+/s',             // shorten multiple whitespace sequences
            //            '/<!--(.|\s)*?-->/',    //strip HTML comments
            '#(?://)?<!\[CDATA\[(.*?)(?://)?\]\]>#s', //leave CDATA alone
        ];
        $replace = [
            '>',
            '<',
            '\\1',
            //            '',
            "//<![CDATA[\n" . '\1' . "\n//]]>",
        ];
        $content = preg_replace($search, $replace, $content);

        return $content;
    }
}
