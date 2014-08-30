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

        $configs = Pi::engine()->application()->getConfig();
        if ($response->isOk()
            && !empty($configs['send_response']['compress'])
            && !ini_get('zlib.output_compression')
        ) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start('ob_gzhandler');
        }
        parent::__invoke($event);

        return $this;
    }
}
