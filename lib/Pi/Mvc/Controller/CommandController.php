<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller;

use Pi;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

/**
 * Command controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
abstract class CommandController extends AbstractActionController
{
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
        $result         = $this->preAction($e);
        if (false !== $result) {
            //$actionResponse = parent::onDispatch($e);

            $routeMatch = $e->getRouteMatch();
            if (!$routeMatch) {
                /**
                 * @todo Determine requirements for when route match is missing.
                 *       Potentially allow pulling directly from request metadata?
                 */
                throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
            }

            $action = $routeMatch->getParam('action', 'not-found');
            $method = static::getMethodFromAction($action);

            if (!method_exists($this, $method)) {
                $method = 'notFoundAction';
            }
            $args = $routeMatch->getParam('args', []);

            $actionResponse = call_user_func_array([$this, $method], $args);
            $e->setResult($actionResponse);

            $this->postAction($e, $actionResponse);
        }

        return $actionResponse;
    }

    public function preAction($e)
    {
        Pi::service('log')->mute();
    }

    public function postAction($e)
    {
        return true;
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
     * @param  string $name
     * @param  array $options
     * @return Pi\Application\Model\Model
     */
    public function getModel($name, $options = [])
    {
        return Pi::db()->model($this->getModule() . '/' . $name, $options);
    }
}