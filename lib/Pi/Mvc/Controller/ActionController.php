<?php
/**
 * Action controller class
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
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\AbstractController;
use Zend\EventManager\EventManagerInterface;

use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;

/**
 * Basic action controller
 */
abstract class ActionController extends AbstractController
{
    protected $skipExecute;

    /**
     * Default action if none provided
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->view()->getViewModel(array(
            'content' => 'Placeholder page'
        ));
    }

    /**
     * Action called if matched action does not exist
     *
     * @return array
     */
    public function notFoundAction()
    {
        $this->jumpTo404();
        return;
        
        $response   = $this->response;
        $event      = $this->getEvent();
        $routeMatch = $event->getRouteMatch();

        $response->setStatusCode(404);
        $routeMatch->setParam('action', 'not-found');

        return $this->view()->getViewModel(array(
            'content' => 'Page not found'
        ));
    }

    /**
     * Dispatch a request
     *
     * @events dispatch.pre, dispatch.post
     * @param  Request $request
     * @param  null|Response $response
     * @return Response|mixed
     */
    public function dispatch(Request $request, Response $response = null)
    {
        $this->request = $request;
        if (!$response) {
            $response = new HttpResponse();
        }
        $this->response = $response;

        $e = $this->getEvent();
        $e->setRequest($request)
          ->setResponse($response)
          ->setTarget($this);

        $result = $this->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH, $e, function($test) {
            return ($test instanceof Response);
        });

        if ($result->stopped()) {
            return $result->last();
        }

        return $e->getResult();
    }

    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws \DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        $actionResponse = null;
        if (!$this->skipExecute) {
            $routeMatch = $e->getRouteMatch();
            if (!$routeMatch) {
                /**
                * @todo Determine requirements for when route match is missing.
                *       Potentially allow pulling directly from request metadata?
                */
                throw new \DomainException('Missing route matches; unsure how to retrieve action');
            }

            $action = $routeMatch->getParam('action', 'not-found');
            $method = static::getMethodFromAction($action);

            if (!method_exists($this, $method)) {
                $method = 'notFoundAction';
            }

            Pi::service('log')->start('ACTIION');
            $actionResponse = $this->$method();
            Pi::service('log')->end('ACTIION');

            $e->setResult($actionResponse);
        }

        // Collect directly returned scalar content
        if (null !== $actionResponse && is_scalar($actionResponse)) {
            $this->view()->setTemplate(false);
            $this->view()->assign('content', $actionResponse);
            $actionResponse = null;
        }
        if (null === $actionResponse && $this->view()->hasViewModel()) {
            $actionResponse = $this->view()->getViewModel();
            $e->setResult($actionResponse);
        }

        return $actionResponse;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return ActionController
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            'Zend\Stdlib\DispatchableInterface',
            __CLASS__,
            get_called_class(),
            substr(get_called_class(), 0, strpos(get_called_class(), '\\')),
            'controller',
        ));
        $this->events = $events;
        $this->attachDefaultListeners();
        return $this;
    }

    /**
     * Skip execute method by detaching listener
     *
     * @return ActionController
     */
    public function skipExecute()
    {
        $this->skipExecute = true;

        return $this;
    }

    /**
     * Get name of current module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->getEvent()->getRouteMatch()->getParam('module');
    }

    /**
     * Get database model
     *
     * @param  string   $name
     * @param  array    $options
     * @return Pi\Application\Model\Model
     */
    public function getModel($name, $options = array())
    {
        return Pi::db()->model($this->getModule() . '/' . $name, $options);
    }

    /**
     * Redirects to a 404 message page
     *
     * @param string $message
     * @return ActionController
     */
    protected function jumpTo404($message)
    {
        $statusCode = 404;
        $this->response->setStatusCode($statusCode);
        $event = $this->getEvent();
        $event->setError(true);
        $this->view()->assign('message', $message);
        return $this;
    }

    /**
     * Redirects to a denied message page
     *
     * @param string $message
     * @return ActionController
     */
    protected function jumpToDenied($message)
    {
        $statusCode = Pi::registry('user')->isGuest() ? 401 : 403;
        $this->response->setStatusCode($statusCode);
        $event = $this->getEvent();
        $event->setError($message);
        return $this;
    }

    /**
     * Redirects to an error message page with specified response code
     *
     * @param string $message
     * @param null|int $code Responsecode
     * @return ActionController
     */
    protected function jumpToException($message, $code = null)
    {
        if ($code) {
            $this->response->setStatusCode($code);
        }
        $event = $this->getEvent();
        $event->setError($message);
        return $this;
    }
}
