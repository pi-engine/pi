<?php
/**
 * Module manager factory
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

namespace Pi\Mvc\Service;

use Zend\Mvc\Service\ModuleManagerFactory as ZendModuleManagerFactory;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModuleManagerFactory extends ZendModuleManagerFactory
{
    /**
     * Creates and returns the module manager
     *
     * Instantiates the default module listeners, providing them configuration
     * from the "module_listener_options" key of the ApplicationConfiguration
     * service. Also sets the default config glob path.
     *
     * Module manager is instantiated and provided with an EventManager, to which
     * the default listener aggregate is attached. The ModuleEvent is also created
     * and attached to the module manager.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ModuleManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /*
        $configuration    = $serviceLocator->get('ApplicationConfiguration');
        $listenerOptions  = new ListenerOptions($configuration['module_listener_options']);
        $defaultListeners = new DefaultListenerAggregate($listenerOptions);
        $serviceListener  = new ServiceListener($serviceLocator, $this->defaultServiceConfiguration);

        $serviceListener->addServiceManager($serviceLocator, 'service_manager', 'Zend\ModuleManager\Feature\ServiceProviderInterface', 'getServiceConfiguration');
        $serviceListener->addServiceManager('ControllerLoader', 'controllers', 'Zend\ModuleManager\Feature\ControllerProviderInterface', 'getControllerConfiguration');
        $serviceListener->addServiceManager('ControllerPluginManager', 'controller_plugins', 'Zend\ModuleManager\Feature\ControllerPluginProviderInterface', 'getControllerPluginConfiguration');
        $serviceListener->addServiceManager('ViewHelperManager', 'view_helpers', 'Zend\ModuleManager\Feature\ViewHelperProviderInterface', 'getViewHelperConfiguration');
        */
        $events        = $serviceLocator->get('EventManager');
        //$events->attach($defaultListeners);
        //$events->attach($serviceListener);

        $moduleEvent   = new ModuleEvent;
        $moduleEvent->setParam('ServiceManager', $serviceLocator);

        //$moduleManager = new ModuleManager($configuration['modules'], $events);
        $moduleManager = new ModuleManager(array());
        $moduleManager->setEvent($moduleEvent);

        return $moduleManager;
    }
}
