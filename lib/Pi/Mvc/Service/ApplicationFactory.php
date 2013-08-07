<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Service;

use Pi\Mvc\Application;
use Zend\Mvc\Service\ApplicationFactory as ZendApplicationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Application factory
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ApplicationFactory extends ZendApplicationFactory
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Application(
            $serviceLocator->get('Config'),
            $serviceLocator
        );
    }
}
