<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc;

use Pi;
use Pi\Application\Engine\AbstractEngine;
use Zend\Mvc\Application as ZendApplication;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Service;
use Zend\ServiceManager\ServiceManager;

/**
 * Application handler
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
     * Set listeners
     *
     * @param string[] $listeners
     * @return $this
     */
    public function setListeners(array $listeners = array())
    {
        if ($listeners) {
            $this->defaultListeners = array_merge(
                $this->defaultListeners,
                $listeners
            );
        }

        return $this;
    }

    /**
     * Load application hander
     *
     * @param array $configuration
     * @return $this
     */
    public static function load($configuration = array())
    {
        $smConfig = isset($configuration['service_manager'])
            ? $configuration['service_manager'] : array();
        $listeners = isset($configuration['listeners'])
            ? $configuration['listeners'] : array();
        $serviceManager = new ServiceManager(
            new Service\ServiceManagerConfig($smConfig)
        );
        //$serviceManager->setService('Configuration', $configuration);
        $serviceManager->get('Configuration')->exchangeArray($configuration);

        return $serviceManager->get('Application')->setListeners($listeners);
    }

    /**
     * Set section, called by Engine
     *
     * @param string $section
     * @return $this
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
     * @return $this
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
     * @return \Zend\Mvc\Router\RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->event->getRouteMatch();
    }

    /**
     * Get router of MvcEvent
     *
     * @return \Zend\Mvc\Router\RouteStackInterface
     */
    public function getRouter()
    {
        return $this->event->getRouter();
    }
    /**#@-*/

    /**#@+
     * Extended from Zend\Mvc\Application
     */
    /**
     * {@inheritdoc}
     */
    protected function completeRequest(MvcEvent $event)
    {
        parent:: completeRequest($event);
        /**
         * Log route information
         */
        if (Pi::service()->hasService('log')) {
            if ($this->getRouteMatch()) {
                Pi::service('log')->info(sprintf(
                    'Route: %s:%s-%s-%s.',
                    $this->getSection(),
                    $this->getRouteMatch()->getParam('module'),
                    $this->getRouteMatch()->getParam('controller'),
                    $this->getRouteMatch()->getParam('action')
                ));
            } else {
                Pi::service('log')->err($event->getError());
            }
        }

        return $this;
    }
    /**#@-*/
}
