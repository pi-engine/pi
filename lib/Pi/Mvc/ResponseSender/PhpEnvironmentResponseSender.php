<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\ResponseSender;

use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\ResponseSender\SendResponseEvent;

class PhpEnvironmentResponseSender extends HttpResponseSender
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response instanceof Response) {
            return $this;
        }
        parent::__invoke($event);

        return $this;
    }
}
