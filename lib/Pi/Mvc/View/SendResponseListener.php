<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Mvc
 */

namespace Pi\Mvc\View;

use Pi;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
//use Zend\Filter\Compress\Gz;

/**
 * @category   Zend
 * @package    Zend_Mvc
 * @subpackage View
 */
class SendResponseListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'compressResponse'), -9999);

        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'sendResponse'), -10000);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Compress response content with PHP zlib
     *
     * Using web server gzip and PHP out_compression is preferred over zlib
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function compressResponse(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response instanceof Response) {
            return;
        }

        $config = $e->getApplication()->getServiceManager()->get('config');
        if (!extension_loaded('zlib') || empty($config['send_response']) || empty($config['send_response']['compress']) ) {
            return;
        }
        $options = $config['send_response']['compress'];
        if (isset($options['mode']) && false === $options['mode']) {
            return;
        }

        $acceptEncoding = $e->getRequest()->getHeaders()->get('Accept-Encoding')->toString();
        if (false === strpos($acceptEncoding, 'gzip')) {
            return;
        }
        if ('production' != Pi::environment() && Pi::service()->hasService('log') && Pi::service('log')->debugger()) {
            return;
        }

        $response = $e->getResponse();
        $content = $response->getContent();
        if ('deflate' == $options['mode']) {
            $content = gzdeflate($content, $options['level']);
        } else {
            $content = gzencode($content, $options['level']);
        }
        $response->setContent($content);
        $response->getHeaders()->addHeaderLine('Content-Encoding', 'gzip');
        return;
    }

    /**
     * Send the response
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function sendResponse(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response instanceof Response) {
            return false; // there is no response to send
        }

        // send the response if possible
        if (is_callable(array($response,'send'))) {
            return $response->send();
        }
    }
}
