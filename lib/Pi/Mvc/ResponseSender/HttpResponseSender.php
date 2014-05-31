<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\ResponseSender;

use Pi;
use Zend\Http\Response;
use Zend\Mvc\ResponseSender\HttpResponseSender as ZendHttpResponseSender;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Filter\Compress\Gz;

class HttpResponseSender extends ZendHttpResponseSender
{
    /**
     * {@inheritDoc}
     *
     * Implement custom gzip in case gzip is not enabled by web server
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response instanceof Response) {
            return $this;
        }
        if (headers_sent() || $event->headersSent()) {
            return $this;
        }
        if ($event->contentSent()) {
            return $this;
        }

        $terminate = false;
        $configs = Pi::engine()->application()->getConfig();
        if (!empty($configs['send_response']['compress'])) {
            $config = $configs['send_response']['compress'];
            if (!isset($config['mode'])) {
                $config['mode'] = 'compress';
            }
            if (false !== $config['mode']) {
                switch ($config['mode']) {
                    case 'deflate':
                        $encoding = 'deflate';
                        break;
                    case 'compress':
                    default:
                        $encoding = 'gzip';
                        $config['mode'] = 'compress';
                        break;
                }
                $acceptEncoding = Pi::engine()->application()->getRequest()->getHeader('accept-encoding');
                if (!$acceptEncoding || !$acceptEncoding->match($encoding)) {
                    $terminate = true;
                }
                if (!$terminate) {
                    try {
                        $compress = new Gz($config);
                    } catch (\Exception $e) {
                        $compress = false;
                    }
                    if ($compress) {
                        $content = $response->getContent();
                        try {
                            $content = $compress->compress($content);
                        } catch (\Exception $e) {
                            $content = false;
                        }
                        if (false !== $content) {
                            $response->setContent($content);
                        }
                        $response->getHeaders()->addHeaderLine('content-encoding', $encoding);
                    }
                }
            }
        }
        parent::__invoke($event);

        return $this;
    }
}
