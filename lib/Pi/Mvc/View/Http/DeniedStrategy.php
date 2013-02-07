<?php
/**
 * Inject template listener
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
 * @since           3.0
 * @package         Pi\Mvc
 * @subpackage      View
 * @version         $Id$
 */

namespace Pi\Mvc\View\Http;

use Zend\View\Model\ViewModel;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;

class DeniedStrategy implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Template to use to display denying messages
     *
     * @var string
     */
    protected $deniedTemplate = 'denied';

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'prepareDeniedViewModel'), -90);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareDeniedViewModel'));
        //$this->listeners[] = $events->attach('complete', array($this, 'prepareDeniedViewModel'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'prepareDeniedViewModel'), -8);
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
     * Get template
     *
     * @param  string $deniedTemplate
     * @return DeniedFoundStrategy
     */
    public function setDeniedTemplate($deniedTemplate)
    {
        $this->deniedTemplate = (string) $deniedTemplate;
        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getDeniedTemplate()
    {
        return $this->deniedTemplate;
    }

    /**
     * Create and return a 404 view model
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareDeniedViewModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response/* || $result instanceof ViewModel*/) {
            // Already have a response or view model as the result
            return;
        }

        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        // Detect 401/403 response if status code is not set
        $errorMessage = $e->getError();
        if (empty($statusCode) && ('__denied__' == $errorMessage)) {
            $statusCode = Pi::registry("user")->isGuest() ? 401 : 403;
            $response->setStatusCode($statusCode);
        }
        if ($statusCode != 401 && $statusCode != 403) {
        //if ($statusCode < 400) {
            // Only handle 401/403 responses
            return;
        }
        if ('__denied__' == $errorMessage) {
            $errorMessage = '';
        }
        if (!$result instanceof ViewModel) {
            $result = new ViewModel();
            if (is_string($errorMessage)) {
                $result->setVariable('message', $errorMessage);
            }
        }
        if ($result->getVariable('message') === null || '__denied__' == $result->getVariable('message')) {
            $result->setVariable('message', $errorMessage ?: '');
        }
        $result->setVariable('code', $statusCode);

        $routeMatch = $e->getRouteMatch();
        if ($routeMatch) {
            $result->setVariable('module', $routeMatch->getParam('module'));
            $result->setVariable('controller', $routeMatch->getParam('controller'));
            $result->setVariable('action', $routeMatch->getParam('action'));
        }
        $result->setTemplate($this->getDeniedTemplate());

        $e->getViewModel()->addChild($result);
    }
}
