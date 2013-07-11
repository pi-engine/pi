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
 * @package         Pi\Mvc
 * @subpackage      Controller
 */

namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

/**
 * Basic action controller
 */
abstract class ActionController extends AbstractActionController
{
    protected $skipExecute;

    /**
     * Action called if matched action does not exist
     *
     * @return array
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
     * @return ActionController
     */
    protected function jumpToDenied($message = '')
    {
        $statusCode = Pi::service('user')->getUser()->isGuest() ? 401 : 403;
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
     * @return ActionController
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
