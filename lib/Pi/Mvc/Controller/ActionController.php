<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

/**
 * Basic action controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class ActionController extends AbstractActionController
{
    /**
     * Whether to skip execution of action
     *
     * @var bool|null
     */
    protected $skipExecute;

    /**
     * Action called if matched action does not exist
     *
     * @return void
     */
    public function notFoundAction()
    {
        $this->jumpTo404();
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
            $actionResponse = parent::onDispatch($e);
            Pi::service('log')->end('ACTIION');
        }

        return $actionResponse;
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
        $event->setError(true);
        $this->view()->assign('message', $message);

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
