<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Command\Mvc\Service;

use Pi\Command\Mvc\Application;
use Laminas\Mvc\Service\ApplicationFactory as ZendApplicationFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Command line Application factory
 *
 * @author Zongshu Lin <lin40553024@163.com>
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
