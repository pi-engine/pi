<?php
/**
 * Pi Application abstraction
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
 * @version         $Id$
 */

namespace Pi\Mvc;

use Pi;
use Pi\Application\Engine\AbstractEngine;
use Zend\Mvc\Application as ZendApplication;
use Zend\Mvc\MvcEvent;

/**
 * Main application class for invoking applications
 *
 * Expects the user will provide a configured ServiceManager, configured with
 * the following services:
 *
 * - EventManager
 * //- ModuleManager
 * - Request
 * - Response
 * - RouteListener
 * - Router
 * - DispatchListener
 * - ViewManager
 *
 * The most common workflow is:
 * <code>
 * $services = new Pi\ServiceManager\ServiceManager($servicesConfig);
 * $app      = new Application($appConfig, $services);
 * $app->bootstrap();
 * $response = $app->run();
 * $response->send();
 * </code>
 *
 * bootstrap() opts in to the default route, dispatch, and view listeners,
 * sets up the MvcEvent, and triggers the bootstrap event. This can be omitted
 * if you wish to setup your own listeners and/or workflow; alternately, you
 * can simply extend the class to override such behavior.
 *
 * @see   Zend\Mvc\Application
 */
class Application extends ZendApplication
{
    /**
     * Section: front, admin, feed, api
     * @var string
     */
    protected $section;

    /**
     * Engine
     * @var AbstractEngine
     */
    protected $engine;

    /**
     * Set section, called by Engine
     *
     * @param string $section
     * @return Application
     */
    public function setSection($section = null)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set application boot engine
     *
     * @param AbstractEngine $engine
     * @return Application
     */
    public function setEngine(AbstractEngine $engine = null)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Get application boot engine
     *
     * @return AbstractEngine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**#@+
     * Syntatic sugar
     */
    /**
     * Get RouteMatch of MvcEvent
     *
     * @return
     */
    public function getRouteMatch()
    {
        return $this->event->getRouteMatch();
    }

    /**
     * Get router of MvcEvent
     *
     * @return type
     */
    public function getRouter()
    {
        return $this->event->getRouter();
    }
    /**#@-*/

    /**
     * Complete the request
     *
     * Triggers "render" and "finish" events, and returns response from
     * event object.
     *
     * @param  MvcEvent $event
     * @return Response
     */
    protected function completeRequest(MvcEvent $event)
    {
        $events = $this->getEventManager();
        $event->setTarget($this);
        /**#@++
         * Trigger events onComplete
         * @see View\DeniedStrategy
         */
        $events->trigger('complete', $event);
        /**#@-*/
        $events->trigger(MvcEvent::EVENT_RENDER, $event);
        $events->trigger(MvcEvent::EVENT_FINISH, $event);

        if (Pi::service()->hasService('log')) {
            if ($this->getRouteMatch()) {
                Pi::service('log')->info(sprintf('Route: %s-%s-%s.', $this->getRouteMatch()->getParam('module'), $this->getRouteMatch()->getParam('controller'), $this->getRouteMatch()->getParam('action')));
            } else {
                Pi::service('log')->err($event->getError());
            }
        }

        return $event->getResponse();
    }
}
