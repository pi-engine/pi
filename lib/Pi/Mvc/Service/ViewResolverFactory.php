<?php
/**
 * View Resolver Factory
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

use Pi;
use Pi\View\Resolver\ModuleTemplate as ModuleTemplateResolver;
use Pi\View\Resolver\ThemeTemplate as ThemeTemplateResolver;
use Pi\View\Resolver\ComponentTemplate as ComponentTemplateResolver;
use Zend\View\Resolver as ViewResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ViewResolverFactory implements FactoryInterface
{
    /**
     * Create the aggregate view resolver
     *
     * Creates a Zend\View\Resolver\AggregateResolver and attaches the template
     * map resolver and path stack resolver
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ViewResolverAggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $resolver = new ViewResolver\AggregateResolver();
        $resolver->attach($serviceLocator->get('ViewTemplateMapResolver'));

        /**#@+
         * Replace with Pi template mechanism
         */
        $moduleTemplateResolver = new ModuleTemplateResolver;
        $themeTemplateResolver = new ThemeTemplateResolver;
        $componentTemplateResolver = new ComponentTemplateResolver;
        $resolver->attach($moduleTemplateResolver);
        $resolver->attach($themeTemplateResolver);
        $resolver->attach($componentTemplateResolver);
        /**#@-*/

        $resolver->attach($serviceLocator->get('ViewTemplatePathStack'));
        return $resolver;
    }
}
