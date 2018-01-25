<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Service;

use Pi\Mvc\Controller\PluginManager;
use Zend\Mvc\Service\AbstractPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Controller plugin manager factory
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ControllerPluginManagerFactory extends AbstractPluginManagerFactory
{
    /**
     * Plugin manager class name
     * @var string
     */
    const PLUGIN_MANAGER_CLASS = 'Pi\Mvc\Controller\PluginManager';

    /**
     * Create and return the MVC controller plugin manager
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return PluginManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $plugins = parent::createService($serviceLocator);

        return $plugins;
    }
}
