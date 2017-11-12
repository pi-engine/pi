<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
