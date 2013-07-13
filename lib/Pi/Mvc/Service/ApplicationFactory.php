<?php
/**
 * Application loader factory
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
 * @package         Pi\Mvc
 */

namespace Pi\Mvc\Service;

use Pi\Mvc\Application;
use Zend\Mvc\Service\ApplicationFactory as ZendApplicationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationFactory extends ZendApplicationFactory
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Application($serviceLocator->get('Config'), $serviceLocator);
    }
}
