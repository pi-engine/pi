<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Service;

use Pi\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Service\AbstractPluginManagerFactory;

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
