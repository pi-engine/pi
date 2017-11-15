<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Command\Mvc;

use Pi;
use Pi\Application\Engine\AbstractEngine;
use Pi\Mvc\Application as PiApplication;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Service;
use Zend\ServiceManager\ServiceManager;

/**
 * Command line Application handler
 *
 * {@inheritDoc}
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Application extends PiApplication
{
    // Default listenser, @see Zend\Mvc\Application
    
    /**
     * Load application handler
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
        
        return $serviceManager->get('Application')->setListeners($listeners);
    }
    
    /**
     * Bootstrap application
     * 
     * @param array $listeners
     * @return \Pi\Command\Mvc\Application
     */
    public function bootstrap(array $listeners = array())
    {
        $serviceManager = $this->serviceManager;
        $events         = $this->events;

        $listeners = array_unique(array_merge($this->defaultListeners, $listeners));

        foreach ($listeners as $listener) {
            $events->attach($serviceManager->get($listener));
        }
        
        // Set custom router
        $router = $serviceManager->get('ConsoleRouter');
        $router->addRoute('Standard', [
            'name' => 'default',
            'type' => 'Pi\Command\Mvc\Router\Http\Standard',
            'options' => [
                'route' => 'default',
            ],
        ], 0);

        // Setup MVC Event
        $this->event = $event  = new MvcEvent();
        $event->setTarget($this);
        $event->setApplication($this)
              ->setRequest($this->request)
              ->setRouter($router);
        
        // Trigger bootstrap events
        $events->trigger(MvcEvent::EVENT_BOOTSTRAP, $event);
        return $this;
    }
}
