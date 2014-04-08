<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Http\PhpEnvironment\Response as HttpResponse;

/**
 * Basic action controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class ActionController extends AbstractActionController
{
    /**
     * @var string
     */
    protected $eventIdentifier = 'PI_CONTROLLER';

    /**
     * Whether to skip execution of action
     *
     * @var bool|null
     */
    protected $skipExecute;

    /**
     * Dispatch a request
     *
     * Stop the event trigger when
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

        /*
        $result = $this->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH, $e, function ($test) {
            return ($test instanceof Response);
        });
        */

        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) use ($e) {
            if ($r instanceof Response) {
                return true;
            }
            if ($e->getError()) {
                return true;
            }
            return false;
        };

        $result = $this->getEventManager()->trigger(
            MvcEvent::EVENT_DISPATCH,
            $e,
            $shortCircuit
        );

        if ($result->stopped()) {
            return $result->last();
        }

        return $e->getResult();
    }

    /**
     * Action called if matched action does not exist
     *
     * @return void
     */
    public function notFoundAction()
    {
        $this->jumpTo404(__('The requested action was not found.'));
        return;
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
            Pi::service('log')->start('ACTIION');
            $result = $this->preAction($e);
            if (false !== $result) {
                $actionResponse = parent::onDispatch($e);
                $this->postAction($e, $actionResponse);
            }
            Pi::service('log')->end('ACTIION');
        }

        return $actionResponse;
    }

    /**
     * Perform tasks before controller action
     *
     * Controller action will be skipped if this method returns false
     *
     * @param  MvcEvent $e
     *
     * @return bool
     */
    protected function preAction($e)
    {
        return true;
    }

    /**
     * Perform tasks after action
     *
     * @param  MvcEvent $e
     * @param mixed $result Action result
     *
     * @return bool
     */
    protected function postAction(MvcEvent $e, &$result)
    {
        return true;
    }

    /**
     * Skip execute method by detaching listener
     *
     * @return $this
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
     * @return $this
     */
    protected function jumpTo404($message = '')
    {
        $statusCode = 404;
        $this->response->setStatusCode($statusCode);
        $event = $this->getEvent();
        $event->setError($message ?: true);
        //$this->view()->assign('message', $message);

        return $this;
    }

    /**
     * Redirects to a denied message page
     *
     * @param string $message
     * @return $this
     */
    protected function jumpToDenied($message = '')
    {
        $statusCode = Pi::service('user')->hasIdentity() ? 403 : 401;
        $this->response->setStatusCode($statusCode);
        $event = $this->getEvent();
        $event->setError($message ?: true);

        return $this;
    }

    /**
     * Redirects to an error message page with specified response code
     *
     * @param string $message
     * @param null|int $code Responsecode
     * @return $this
     */
    protected function jumpToException($message = '', $code = null)
    {
        if ($code) {
            $this->response->setStatusCode($code);
        }
        $event = $this->getEvent();
        $event->setError($message ?: true);

        return $this;
    }
}
